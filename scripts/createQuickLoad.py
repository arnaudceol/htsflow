import urllib2  # the lib that handles the url stuff
import urllib
import os
import pymysql
import configparser
import sys
import glob
import re


configFile = sys.argv[1] # configparser.ConfigParser()
#config.read(sys.argv[1])

paths={}

with open(configFile) as f:
    content = f.readlines()
    for line in content:        
        if not line[0] == ';' and not line[0] == '[' and not len(line.rstrip()) == 0:
            mylist = line.strip().split('=')
            key=mylist[0].strip()
            value=mylist[1].strip()
            if value.startswith("["):
                toReplace=re.sub(r'^\[(.*)\].*$', r'\1', value)
                replacement=paths[toReplace] 
                value='['+replacement+']'+value
                value=re.sub(r'^\[(.*)\]\[.*\](.*)$', r'\1\2', value)
            paths[key] = value



dbConfFile = paths['DB_CONF']
dbConfig = configparser.ConfigParser()
dbConfig.read(dbConfFile)

# Get synonyms from IGB
igbSynonymsUrl = "https://bitbucket.org/lorainelab/integrated-genome-browser/raw/1194f9df27bb771cac7422af28c673a46a4d22b4/core/synonym-lookup/src/main/resources/synonyms.txt"

# Get genomes from HTS flow
synomymToGenome = {}




tracksBwUrl =  "http://www.bioinfo.ieo.eu/BAgroup/analysis/HTS-flow/primary/tracks/bw/" #paths['HTSFLOW_WEB_TRACKS'] + "/primary/tracks/bw/" #
tracksSecondaryUrl = "http://www.bioinfo.ieo.eu/BAgroup/analysis/HTS-flow/secondary/" #+ paths['HTSFLOW_WEB_TRACKS'] + "/secondary/"  #

data = urllib2.urlopen(igbSynonymsUrl) # it's a file like object and works just like a file
for line in data: # files are iterable
    synonyms = line.strip().split('\t')
    igbGenomeName = synonyms[0]
    for synonym in synonyms:
        synomymToGenome[synonym] = igbGenomeName


# for each genome, create a directory


database = dbConfig.get('database', 'dbname')
host =  dbConfig.get('database', 'hostname')
user = dbConfig.get('database', 'username')
password = dbConfig.get('database', 'pass')

dbHTSflow = pymysql.connect(host = host, db = database, user=user, passwd=password)
dbHTSflow.autocommit(True)
cHTSflow = dbHTSflow.cursor()

sqlQuery = "select distinct ref_genome FROM sample"
cHTSflow.execute(sqlQuery)
htsFlowGenomes = cHTSflow.fetchall()

#print htsFlowGenomes

# prepare jobs
igbGenomesToLoad = {}
for htsFlowGenomeRow in htsFlowGenomes:
    htsFlowGenome = htsFlowGenomeRow[0]
    print(htsFlowGenome)
    if htsFlowGenome in synomymToGenome.keys():
        igbGenomesToLoad[synomymToGenome[htsFlowGenome]] = 'yes'





# Read contents file for IGB quickload, and copy the lines we need to the local contents.txt file
igbContentUrl = "http://igbquickload.org/quickload/contents.txt"

f = open('contents.txt', 'w')

data = urllib2.urlopen(igbContentUrl) # it's a file like object and works just like a file
for line in data: # files are iterable
    (genome, description) = line.strip().split('\t')
    if genome in igbGenomesToLoad.keys():
        f.write(line)

f.close()


# download genome files:
#http://igbquickload.org/A_thaliana_Jan_2004/genome.txt
for genome in igbGenomesToLoad.keys():
    #print(genome)
    if not os.path.exists(genome):
        os.makedirs(genome)
    sourceUrl = "http://igbquickload.org/%s/genome.txt" % genome
    targetFile = "%s/genome.txt" % genome
    urllib.urlretrieve (sourceUrl, targetFile)


# For each genome, create a annots.xml
for htsFlowGenomeRow in htsFlowGenomes:
    # get type, primary id and label 
    htsFlowGenome = htsFlowGenomeRow[0]
    
    if not htsFlowGenome in synomymToGenome:
        print("Skip %s: not in IGB" % htsFlowGenome)
        continue
    genome = synomymToGenome[htsFlowGenome]

    fileName = "%s/annots.xml" % genome
    print("write " + fileName)
    f = open(fileName, 'w')

    f.write("<files>\n")


    # Primary
    sqlQuery = "SELECT user_name, primary_analysis.id, sample_name, seq_method, stranded FROM sample, users, primary_analysis, pa_options WHERE pa_options.id = options_id AND sample_id = sample.id AND primary_analysis.user_id = users.user_id AND ref_genome = '%s' AND status ='completed' ORDER BY user_name, primary_analysis.id" % htsFlowGenomeRow 
    #print(sqlQuery)

    cHTSflow.execute(sqlQuery)
    primaryAnalysisRows = cHTSflow.fetchall()
    for primaryAnalyses in primaryAnalysisRows:
        userName = primaryAnalyses[0]
        primaryId = primaryAnalyses[1]
        sampleName =  primaryAnalyses[2]
        seqMethod = primaryAnalyses[3]
	stranded = primaryAnalyses[4]
        # by user
        userInput = "  <file title=\"users/%s/primary/%s-%s\" name=\"%s%s.bw\"/>\n" % (userName, primaryId, sampleName, tracksBwUrl, primaryId)
        # by method
        methodInput = "  <file title=\"primary/%s/%s-%s\" name=\"%s%s.bw\"/>\n" % (seqMethod, primaryId, sampleName, tracksBwUrl, primaryId)

        f.write(userInput)
        f.write(methodInput)
        
	if stranded == 1:
	        # by user
        	userInput = "  <file title=\"users/%s/primary/%s-%s +\" name=\"%s%s_p.bw\"/>\n" % (userName, primaryId, sampleName, tracksBwUrl, primaryId)
		f.write(userInput)
        	userInput = "  <file title=\"users/%s/primary/%s-%s -\" name=\"%s%s_n.bw\"/>\n" % (userName, primaryId, sampleName, tracksBwUrl, primaryId)
		f.write(userInput)
	        # by method
	        methodInput = "  <file title=\"primary/%s/%s-%s +\" name=\"%s%s_p.bw\"/>\n" % (seqMethod, primaryId, sampleName, tracksBwUrl, primaryId)
	        f.write(methodInput)
	        methodInput = "  <file title=\"primary/%s/%s-%s -\" name=\"%s%s_n.bw\"/>\n" % (seqMethod, primaryId, sampleName, tracksBwUrl, primaryId)
	        f.write(methodInput)



    # Primary
    sqlQuery = "SELECT DISTINCT user_name, secondary_analysis.id,  method \
    FROM sample, users, primary_analysis, secondary_analysis, peak_calling \
     WHERE sample_id = sample.id AND secondary_analysis.user_id = users.user_id \
         AND secondary_id = secondary_analysis.id AND primary_id = primary_analysis.id AND ref_genome = '%s' AND secondary_analysis.status ='completed'  ORDER BY user_name, secondary_analysis.id" % htsFlowGenomeRow 
    #print(sqlQuery)

    cHTSflow.execute(sqlQuery)
    primaryAnalysisRows = cHTSflow.fetchall()
    for primaryAnalyses in primaryAnalysisRows:
        userName = primaryAnalyses[0]
        primaryId = primaryAnalyses[1]
        #sampleName =  primaryAnalyses[2]
        seqMethod = primaryAnalyses[2]
        # by user
        # Parse bb files
        print("search files for : " + str(primaryId))
        #os.chdir("/mydir")
        print("search in: " + paths['HTSFLOW_BED'] + str(primaryId) + "/bed/*.bb") 
        for path in glob.glob(paths['HTSFLOW_BED'] + str(primaryId) + "/bed/*.bb"):            
            fileName = os.path.basename(path)   
            print(path)         
            userInput = "  <file title=\"users/%s/secondary/%s-%s\" name=\"%s/%s/bed/%s\"/>\n" % (userName, primaryId, fileName, tracksSecondaryUrl, primaryId,fileName)            
            # by method
            methodInput = "  <file title=\"secondary/%s/%s-%s\" name=\"%s/%s/bed/%s\"/>\n" % (seqMethod, primaryId, fileName, tracksSecondaryUrl, primaryId, fileName)
            f.write(userInput)
            f.write(methodInput)
            

        
    f.write("</files>\n")
    f.close()
    
#<files>
#  <file name="bamfiles/Treatment1.bam"
#   title="RNA-Seq/Treatment Sample 1"
#   description="RNA-Seq alignments from Treatment Sample 1"
#   background="FFFFFF"
#   foreground="0000FF"
#   max_depth="15"
#   name_size="12"/>
#</files>



