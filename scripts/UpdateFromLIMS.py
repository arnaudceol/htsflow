#! /usr/bin/python

# TRY WITH A FIND FROM THE SHELL TO IDENTIFY WHICH FOLDER BELONG TO WHICH SAMPLE
# AND RETRIEVE IN THIS WAY THE FASTQ FOLDER.

# this program will check the entries in the HTS-flow DB, comparing them with
# the LIMS system. If there are differences (e.g. new entries), it will
# generate the SQL file called HTSentriesUpdate.sql. Remember to  run
# mysql to fill the HTS-flow DB with the new ones.

import sys
import os
import MySQLdb
import commands
import configparser
import datetime

def main(dbConf):
	
	config = configparser.ConfigParser()
	config.read(dbConf)

	database = config.get('database', 'dbname')
	host =  config.get('database', 'hostname')
	user = config.get('database', 'username')
	password = config.get('database', 'pass')

		
	databaseLIMS = config.get('lims', 'dbname')
	hostLIMS =  config.get('lims', 'hostname')
	userLIMS = config.get('lims', 'username')
	passwordLIMS = config.get('lims', 'pass')

	print("Updating database %s on %s" % (database, host))   
	print("LIMS database %s on %s" % (databaseLIMS, hostLIMS))   

	dbLims = MySQLdb.connect(host = hostLIMS, db = databaseLIMS, user=userLIMS, passwd=passwordLIMS)
	cLims = dbLims.cursor()
	
	dbHTSflow = MySQLdb.connect(host = host, db = database, user=user, passwd=password)
	dbHTSflow.autocommit(True)	#db.autocommit(True) # this is fundamental. mysql interface do not commit by default -__-
	cHTSflow = dbHTSflow.cursor()


	########################
	# selection of the first scheduled element
	stringa1 = "select c.sampleID,c.sampleName,s.FCID,c.readLength,c.readMode,c.depth,s.sample_project,c.userName,c.userEmail,s.sampleRef,c.application,s.date from chipseq c, samplesheet s where (c.PI = \"BA\" or c.PI = \"STC\" )  and c.sampleID = s.counter order by s.date DESC"
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

	# Should we add a user
	for elem in limsEntries:
		# in the LIMS, the user name is in the column sample_project
		user_name = str(elem[6]).strip()
		if user_name not in htsFlowUsers:
			# insert
			queryAddUser = "INSERT INTO users(user_name) VALUES ('%s')" % user_name
			print("Add user %s: %s" % (user_name, queryAddUser))
			cHTSflow.execute(queryAddUser)
			# add to list 
			htsFlowUsers.append(user_name)

	for elem in limsEntries:
		id_sample = str(elem[0]).strip()
		app = elem[10] # seq_method			
		if app not in APPLIST:
			APPLIST.append(app)

	for elem in limsEntries:
		id_sample = str(elem[0]).strip()
		if id_sample not in HTSsamples:
			sample_name = elem[1].replace(" ","")
			FCID = elem[2]
			readL = elem[3]
			readmode = elem[4]
			depth= elem[5]
			# in the LIMS, the user name is in the column sample_project
			sampleProject = elem[6]
			user = elem[7]
			usermail = elem[8]
			refgen = elem[9]
			app = elem[10] # seq_method			
			pieces = commands.getstatusoutput( "find /Illumina/PublicData/Amati/ . -name *%s*  |grep '/FASTQ/'"%(sample_name) )[1].split("\n")[0]
			FOLD = pieces
			print "%s not in HTS-flow. Checking for directory.. "%id_sample
			if not os.path.exists(FOLD):
				print ".. directory %s does not exist"%FOLD
				print id_sample, sample_name,FCID,readL,readmode,depth,sampleProject,user,usermail,refgen,app
				#find /Illumina/PublicData/Amati/ . -name *S_0h_wt_myc_S8675* -exec ls {} \;
				#FOLD = "/Illumina/PublicData/Amati/%s/FASTQ/%s/Sample_%s/"%(sampleProject,FCID,sample_name)
				FOLD = "-"
			if FOLD != "-":
				updateTime = datetime.datetime.fromtimestamp(os.path.getmtime(FOLD))
				print ".. directory %s found. Writing the MYSQL code."%FOLD
				stringa = "INSERT INTO sample (id, sample_name, seq_method, reads_length, reads_mode, ref_genome, raw_data_path, user_id, source, raw_data_path_date) SELECT \"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\", users.user_id, 0, '%s' FROM users WHERE users.user_name = '%s';" % (id_sample, sample_name, app, readL, readmode, refgen, FOLD, updateTime, sampleProject)
				cHTSflow.execute(stringa)

	# fix integrity of seq_method to match RNA-Seq and ChIP-Seq signature.
	stringaRNA = "UPDATE sample SET seq_method=\"RNA-Seq\" where seq_method LIKE \"%RNA%\";"
	stringaChIP = "UPDATE sample SET seq_method=\"ChIP-Seq\" where seq_method LIKE \"%ChIP%\";"
	stringaDNA = "UPDATE sample SET seq_method=\"DNA-Seq\" where seq_method LIKE \"%DNA-Seq%\";"
	cHTSflow.execute(stringaRNA)
	cHTSflow.execute(stringaChIP)
	cHTSflow.execute(stringaDNA)	
	

	############# OUTPUT MESSAGE #############

	if len(LIMSlista) != 0:
		print "\n%s new entries in the LIMS have been inserted in the HTS-flow DB."%len(LIMSlista)
	else:
		print "\nThere are no new entries in the LIMS. Live long and prosper.\n"

if __name__ == "__main__":
	configfile = sys.argv[1]
	main(configfile)
