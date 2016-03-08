# Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

#
# sampleId: a temporary id for HTS-flow
#
geoDownload <- function( sampleId ) {
    
    ## outpath: for preprocess files
    # outPath <- getPreprocessDir()
	## for temporary files
    # tmpFold <- getHTSFlowPath("HTSFLOW_TMP")

	loginfo(paste("Start downloading GEO, sample: ", sampleId ))
	
	# There ither one GSE	
	gse <-''
	# or a list of GSMs
	gsms = c()
		
	
	# Get ids, in the database they are store in the sample description
	# as a list of type:value, separated by ,
	# where type is either gse or gsm. There may be only one gsm
	sqlGeoIds <- paste0("SELECT description FROM sample_description WHERE sample_id = '",sampleId,"'") 
	
	sampleDescription <- extractSingleColumnFromDB(sqlGeoIds)
	
	geoIds <- strsplit(sampleDescription, ';')[[1]]
	for(id in geoIds) {
		geoType <- strsplit(id, ':')[[1]][0]
		geoId <- strsplit(id, ':')[[1]][1]
		if (geoType == 'gse') {
			gse <- geoId
		} else {
			gsms <- c(gsms, geoId)
		}	
	} 
		
	# External user dir: 	
	user<-Sys.getenv("USER")
	uploadDir <- getHTSFlowPath("HTSFLOW_UPLOAD_DIR")	
	userUploadDir<-paste0(uploadDir, "/", user)
  
	
	## Get the list of GSMs if neccessary	
	if (gse == '') {
		# Load list of GSMs
		# ....		
	}
	
	# load config and common functions
	workdir <- getwd()
	setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
	
	loadConfig("BatchJobs.R")
	setwd(workdir)
	
	regName <- paste0("HF_GEO_",sampleID)
	
	reg <- makeHtsflowRegistry(regName)
	
	numjobs <- length(gsms)
	
	ids <- batchMap(reg, fun=peakcaller, rep(gse, numjobs), gsms, rep(userUploadDir, numjobs))
	submitJobs(reg)
	waitForJobs(reg)
	
	loginfo(paste0("Status of jobs for registry %s: ", regName))
	
	showStatus(reg)
	
	errors<-findErrors(reg)
	
	if (length(errors) > 0) {
		stop(paste0(length(errors), " job(s) failed, exit"))
	}
	
}
	
downloadGSM <- function( gse, gsm , userUploadDir) {
	
	# Get new sample ID
	sqlSampleId <- "SELECT nextSampleId();";
	sampleId <- extractSingleColumnFromDB(sqlSampleId)
	
	loginfo(paste0("Create new sample: ", sampleId, " for GSM ", gse, "/", gsm))
	
	user<-Sys.getenv("USER")
	
	
	# Get info: 
	#...
	
	# rna-seq, chip-seq etc.
	method <- ''
	# mm9, hg19 ...
	refGenome <- ''
	# read length
	readLength <- ''
	# PE or SE
	readMode <- ''
	# read data path
	path <- paste0(userUploadDir, "/", gse, "/", gsm)
	
	# Description (may be on several lines
	description <- ''
	
	
	# Download
	#....
	
	
	sqlSample <- paste0('INSERT INTO sample (id, sample_name, seq_method, reads_length, reads_mode, ref_genome, raw_data_path, user_id, source, raw_data_path_date) ', 
			"SELECT 'X" , sampleId, "', '" , gsm , "','" , method , "','" , 
			readLength , "','" , readMode , "','" ,refGenome  , "','" , path, "', user_id, 2, '", date('Y-m-d H:i:s') ,"' FROM users WHERE user_name = '" , user , "'")
	
	res <- updateInfoOnDB( sqlSample )
	
	
	sqlDescription <- paste0("INSERT INTO sample_description (sample_id, description) VALUES '", sampleId, "', '", description, "'")
	
  	res <- updateInfoOnDB( sqlDescription )
	
	
	loginfo(paste0("Create new sample: ", sampleId, " for GSM ", gse, "/", gsm, "added in directory ", path))
	
}
	
	