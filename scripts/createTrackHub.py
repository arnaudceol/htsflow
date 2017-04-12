#import urllib2  # the lib that handles the url stuff
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


hubName="htsflow%s" % paths['HTSFLOW_NAME']

hubDir = "gb_htsflow_%s" % paths['HTSFLOW_NAME']

tracksBwUrl =  "http://www.bioinfo.ieo.eu/BAgroup/analysis/HTS-flow/primary/tracks/bw/" #paths['HTSFLOW_WEB_TRACKS'] + "/primary/tracks/bw/" #
tracksSecondaryUrl = "http://www.bioinfo.ieo.eu/BAgroup/analysis/HTS-flow/secondary/" #+ paths['HTSFLOW_WEB_TRACKS'] + "/secondary/"  #


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
p = re.compile('^[a-zA-Z]+[0-9]+$', re.IGNORECASE)

for htsFlowGenomeRow in htsFlowGenomes:
    if p.match(htsFlowGenomeRow[0]):  
        igbGenomesToLoad[htsFlowGenomeRow[0]] = 'yes'


# 
# # Read contents file for IGB quickload, and copy the lines we need to the local contents.txt file
# igbContentUrl = "http://igbquickload.org/quickload/contents.txt"
# 
# f = open('contents.txt', 'w')
# 
# data = urllib2.urlopen(igbContentUrl) # it's a file like object and works just like a file
# for line in data: # files are iterable
#     (genome, description) = line.strip().split('\t')
#     if genome in igbGenomesToLoad.keys():
#         f.write(line)
# 
# f.close()

if not os.path.exists(hubDir):
    os.makedirs(hubDir)


hubFile = open('%s/hub.txt' % hubDir, 'w')
genomesFile = open('%s/genomes.txt' % hubDir, 'w')

hubFile.write("hub %s\n" % hubName)
hubFile.write("shortLabel %s\n" % hubName)
hubFile.write("longLabel HTS-flow track hub\n")
hubFile.write("email %s\n" % "arnaud.ceol@iit.it")
hubFile.write("genomesFile genomes.txt\n")

hubFile.close()

# download genome files:
#http://igbquickload.org/A_thaliana_Jan_2004/genome.txt
for genome in igbGenomesToLoad.keys():
    #print(genome)
    genomeDir = "%s/%s" %(hubDir, genome)
    if not os.path.exists(genomeDir):
        os.makedirs(genomeDir)
        
    genomesFile.write("genome %s\n" % genome)
    genomesFile.write("trackDb %s/trackDb.txt\n" % genome)
    genomesFile.write("\n")
        
genomesFile.close()              

# For each genome, create a annots.xml
for htsFlowGenomeRow in htsFlowGenomes:
    
    # get type, primary id and label
    genome = htsFlowGenomeRow[0]
 
    fileName = "%s/%s/trackDb.txt" % (hubDir,genome)

    print("write " + fileName)
    f = open(fileName, 'w')

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
        
        f.write("track %s\n" % primaryId)
        f.write("bigDataUrl %s%s.bw\n" % (tracksBwUrl, primaryId))
        f.write("shortLabel P%s-%s-%s\n" % (primaryId, seqMethod, userName))
        f.write("longLabel %s %s %s (%s)\n" % (primaryId, sampleName, seqMethod, userName))
        f.write("type %s\n" % "bigWig")
        f.write("\n")
        
        if stranded == 1:
            f.write("track %s_p\n" % primaryId)
            f.write("bigDataUrl %s%s_p.bw\n" % (tracksBwUrl, primaryId))
            f.write("shortLabel P%s-%s-%s (+)\n" % (primaryId, seqMethod, userName))
            f.write("longLabel %s %s %s positive strand (%s)\n" % (primaryId, sampleName, seqMethod, userName))
            f.write("type %s\n" % "bigWig")
            f.write("")
	       
            f.write("track %s_n\n" % primaryId)
            f.write("bigDataUrl %s%s_n.bw\n" % (tracksBwUrl, primaryId))
            f.write("shortLabel P%s-%s-%s (-)\n" % (primaryId, seqMethod, userName))
            f.write("longLabel %s %s %s negative strand (%s)\n" % (primaryId, sampleName, seqMethod, userName))
            f.write("type %s\n" % "bigWig")
            f.write("\n")
     
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
        for path in glob.glob(paths['HTSFLOW_BED'] + str(primaryId) + "/bed/*.bb"):            
            fileName = os.path.basename(path)
            f.write("track S%s-%s-%s-%s\n" % (primaryId, seqMethod, fileName, userName))
            f.write("bigDataUrl %s/%s/bed/%s\n" % (tracksSecondaryUrl, primaryId,fileName))
            f.write("shortLabel S%s-%s-%s-%s\n" % (primaryId, seqMethod, fileName, userName))
            f.write("longLabel %s %s %s (%s)\n" % (primaryId, seqMethod, fileName, userName))
            f.write("type %s\n" % "bigBed")
            f.write("\n")
    f.close()
    
