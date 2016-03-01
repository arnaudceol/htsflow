#! /usr/bin/python

# TRY WITH A FIND FROM THE SHELL TO IDENTIFY WHICH FOLDER BELONG TO WHICH SAMPLE
# AND RETRIEVE IN THIS WAY THE FASTQ FOLDER.

# this program will check the entries in the HTS-flow DB, comparing them with
# the LIMS system. If there are differences (e.g. new entries), it will
# generate the SQL file called HTSentriesUpdate.sql. Remember to  run
# mysql to fill the HTS-flow DB with the new ones.

import sys
import os
import pymysql
import commands
import configparser
import datetime
import re

def main(configFile):
	
	config = configparser.ConfigParser()
	config.read(configFile)

	dbConfFile = config.get('htsflow', 'DB_CONF')

	# User name of the PIs to import from the LIMS
	pisQuery = ""
	if config.has_option('lims', 'PI') and config.get('lims', 'PI').strip() != '' :
		pis = re.split(" *\, *", config.get('lims', 'PI').strip()) 
		print("import from LIMS: PIS in %s", ",".join(pis))
		pisQuery = " AND pi.login IN ('%s')" % "', '".join(pis); 
	

	fastqLimsDir = config.get('lims', 'FASTQ_LIMS_DIR')
	
	
	dbConfig = configparser.ConfigParser()
	dbConfig.read(dbConfFile)

	database = dbConfig.get('database', 'dbname')
	host =  dbConfig.get('database', 'hostname')
	user = dbConfig.get('database', 'username')
	password = dbConfig.get('database', 'pass')

		
	databaseLIMS = dbConfig.get('lims', 'dbname')
	hostLIMS =  dbConfig.get('lims', 'hostname')
	userLIMS = dbConfig.get('lims', 'username')
	passwordLIMS = dbConfig.get('lims', 'pass')

	print("Updating database %s on %s" % (database, host))   
	print("LIMS database %s on %s" % (databaseLIMS, hostLIMS))   

	dbLims = pymysql.connect(host = hostLIMS, db = databaseLIMS, user=userLIMS, passwd=passwordLIMS)
	cLims = dbLims.cursor()

	dbHTSflow = pymysql.connect(host = host, db = database, user=user, passwd=password)
	dbHTSflow.autocommit(False)	#db.autocommit(True) # this is fundamental. mysql interface do not commit by default -__-
	cHTSflow = dbHTSflow.cursor()


	########################
	# selection of the first scheduled element
	stringa1 = "SELECT sample.sam_id, sample.name, samplerun.flowcell, application.readlength, application.readmode,application.depth,  user.login, user.username, user.mailadress, organism,  \
application.applicationname, pi.login, runfolder \
FROM sample, samplerun, application, user, user as pi \
WHERE application.application_id = sample.application_id AND samplerun.sam_id = sample.sam_id \
AND sample.requester_user_id = user.user_id AND pi.user_id = user.pi \
AND status = 'analyzed' %s \
order by requestdate DESC;" % pisQuery;
	print(stringa1)
	cLims.execute(stringa1)
	limsEntries = cLims.fetchall()

	########################

	# selection of the first scheduled element
	folder = "/Illumina/PublicData/SampleSheets/"

	stringa = "select id from sample"
	cHTSflow.execute(stringa)
	samples = cHTSflow.fetchall()

	HTSsamples = []
	for elem in samples:
		HTSsamples.append(str(elem[0]))

	LIMSlista = []
	# we checked all the data if it not already inserted in the DB.
	# SeqMethodAllowed = []
	#SeqMethodAllowed = ['ChIP-Seq', 'RNA-Seq', 'DNA-Seq', 'mRNA-Seq', 'ExomeSeq', 'RNAseq', 'ChIPSeq', 'RNASeq', 'ChIPseq', 'DNAseq', 'DNase-Seq, Sono-Seq and FAIrE-Seq', 'Targeted DNA-Seq' ]
	
	for elem in limsEntries:
		id_sample = str(elem[0]).strip()
		if id_sample not in LIMSlista:
			LIMSlista.append(id_sample)

	APPLIST = []

	# Get users from HTS-flow
	queryHtsFlowUsers = "SELECT DISTINCT user_name FROM users";
	cHTSflow.execute(queryHtsFlowUsers)
	
	htsFlowUsers=[]
	for user in cHTSflow.fetchall():
		htsFlowUsers.append(user[0])
	
	print("Number of users in HTSflow: %s" % (len(htsFlowUsers)))

	numNewUsers = 0
	# Should we add a user
	for elem in limsEntries:
		# in the LIMS, the user name is in the column sample_project
		user_name = str(elem[6]).strip()
		if user_name not in htsFlowUsers:
			# insert
			queryAddUser = "INSERT INTO users(user_name) VALUES ('%s')" % user_name
			print("Add user %s: %s" % (user_name, queryAddUser))
			numNewUsers = numNewUsers + 1
			cHTSflow.execute(queryAddUser)
			# add to list 
			htsFlowUsers.append(user_name)

	for elem in limsEntries:
		id_sample = str(elem[0]).strip()
		app = elem[10] # seq_method			
		if app not in APPLIST:
			APPLIST.append(app)

	numAdded = 0
	numFileNotFound = 0
	numNoRunId = 0

	for elem in limsEntries:
		id_sample = str(elem[0]).strip()
		if id_sample not in HTSsamples:
			sample_name = elem[1].replace(" ","")
			FCID = elem[2]
			readL = elem[3]
			readmode = elem[4]
			depth= elem[5]
			# in the LIMS, the user name is in the column sample_project
			user = elem[6]
			userName = elem[7]
			usermail = elem[8]
			refgen = elem[9]
			app = elem[10] # seq_method	
			pi = elem[11]
			runFolder = elem[12]
			runId = runFolder.split("_")[0].strip()		

			if runId == "":
				numNoRunId = numNoRunId+1
				#print("Ignore missing runId: sample %s" % sample_name)
				continue
			#pieces = commands.getstatusoutput( "find /Illumina/PublicData/Amati/ . -name *%s*  |grep '/FASTQ/'"%(sample_name) )[1].split("\n")[0]
			#FOLD = pieces
			FOLD = "%s/%s/FASTQ/%s/Sample_%s" % (fastqLimsDir, user, runId, sample_name)
			print "%s not in HTS-flow. Checking for directory.. " % (id_sample)
			if not os.path.exists(FOLD):
				print ".. directory %s does not exist"%FOLD
				#print id_sample, sample_name,FCID,readL,readmode,depth,sampleProject,user,usermail,refgen,app
				#find /Illumina/PublicData/Amati/ . -name *S_0h_wt_myc_S8675* -exec ls {} \;
				#FOLD = "/Illumina/PublicData/Amati/%s/FASTQ/%s/Sample_%s/"%(sampleProject,FCID,sample_name)
				numFileNotFound = numFileNotFound + 1
				FOLD = "-"
			if FOLD != "-":
				updateTime = datetime.datetime.fromtimestamp(os.path.getmtime(FOLD))
				print ".. directory %s found. Writing the MYSQL code."%FOLD
				stringa = "INSERT INTO sample (id, sample_name, seq_method, reads_length, reads_mode, ref_genome, raw_data_path, user_id, source, raw_data_path_date) SELECT \"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\", users.user_id, 0, '%s' FROM users WHERE users.user_name = '%s';" % (id_sample, sample_name, app, readL, readmode, refgen, FOLD, updateTime, user)
				print("SQL: %s" %stringa)
				numAdded = numAdded+1
				cHTSflow.execute(stringa)
				

	# fix integrity of seq_method to match RNA-Seq and ChIP-Seq signature.
	stringaRNA = "UPDATE sample SET seq_method=\"rna-seq\" where seq_method LIKE \"%RNA%\";"
	stringaChIP = "UPDATE sample SET seq_method=\"chip-seq\" where seq_method LIKE \"%ChIP%\";"
	stringaDNA = "UPDATE sample SET seq_method=\"dna-seq\" where seq_method LIKE \"%DNA-Seq%\";"
	stringaDNAse = "UPDATE sample SET seq_method=\"dnase-seq\" where seq_method LIKE \"%DNAseI-Seq%\";"
	stringaBS = "UPDATE sample SET seq_method=\"bs-seq\" where seq_method LIKE \"%BS-Seq%\";"
	cHTSflow.execute(stringaRNA)
	cHTSflow.execute(stringaChIP)
	cHTSflow.execute(stringaDNA)
	cHTSflow.execute(stringaDNAse)		
	cHTSflow.execute(stringaBS)	
	
	# Set default ref genome. In the future we should move it to the primary.
	stringaRefGenome = "UPDATE sample SET ref_genome =\"hg19\" where ref_genome = \"HUMAN\";"
	cHTSflow.execute(stringaRefGenome)	
	stringaRefGenome = "UPDATE sample SET ref_genome =\"mm9\" where ref_genome = \"MOUSE\";"
	cHTSflow.execute(stringaRefGenome)
	stringaRefGenome = "UPDATE sample SET ref_genome =\"dm6\" where ref_genome = \"DROSOPHILA\";"
	cHTSflow.execute(stringaRefGenome)


	dbHTSflow.commit()

	############# OUTPUT MESSAGE #############

	if len(LIMSlista) != 0:
		print "\n%s new entries have been inserted in HTS-flow, %s have been skiped because the file was not available, %s because no runId was found. %s new users inserted"% (numAdded, numFileNotFound,numNoRunId, numNewUsers)
	else:
		print "\nThere are no new entries in the LIMS. Live long and prosper.\n"

if __name__ == "__main__":
	configfile = sys.argv[1]
	main(configfile)
