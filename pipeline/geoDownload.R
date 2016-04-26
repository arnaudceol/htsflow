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


library('GEOmetadb')
library('SRAdb')
library('R.utils')

####
####
#### Use a new table rather than the sample one.
####
geoDownload <- function( taskId ) {
	
	## outpath: for preprocess files
	# outPath <- getPreprocessDir()
	## for temporary files
	# tmpFold <- getHTSFlowPath("HTSFLOW_TMP")
	
	loginfo(paste("Start downloading GEO, task: ", taskId ))
	
	# There ither one GSE	
	gse <-''
	# or a list of GSMs
	gsms = c()
	
	
	# Get ids, in the database they are store in the sample description
	# as a list of type:value, separated by ,
	# where type is either gse or gsm. There may be only one gsm
	sqlGeoIds <- paste0("SELECT description FROM other_analysis WHERE id = '", taskId,"'") 
	
	taskDescription <- extractSingleColumnFromDB(sqlGeoIds)
	loginfo(taskDescription)
	geoIds <- strsplit(taskDescription[1,], ';')[[1]]
	for(id in geoIds) {
		geoType <- substr(id, 1,3) # strsplit(id, ':')[[1]][0]
		if (geoType == 'GSE') {
			gse <- id
		} else if (geoType == 'GSM')  {
			gsms <- c(gsms, id)
		}	
	} 
	
	# External user dir: 	
	user<-Sys.getenv("USER")
	uploadDir <- unlist(strsplit(getHTSFlowPath("HTSFLOW_UPLOAD_DIR"), ","))[1]	
	
	userUploadDir<-paste0(uploadDir, "/", user)

	# Create it if it does not exist
	if (! file.exists(userUploadDir)){
		loginfo(paste("create directory: ", uploadDir))
		createDir(userUploadDir, recursive=TRUE)		
	}
	
	
	## Get the list of GSMs if neccessary	
	if (gse != '') {
		#Find the list of GSMs associated to a given gse 
		geo_con <- connectToGEOmetaDB()
		gsms <- dbGetQuery(geo_con, paste0("select distinct gsm from gse_gsm where gse ='", gse,"'"))[,1]
		if(length(gsms)==0){
			# throw new exception (do we throw exceptions?)
			loginfo("Invalid GSE id")
		}		
	}
	
	loginfo(paste0("Download dir: " , userUploadDir))
	
	#createDir(userUploadDir)
	
	##downloadGSM(gse, gsms[1], userUploadDir)
	
	# load config and common functions
	workdir <- getwd()
	setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
	
	loadConfig("BatchJobs.R")
	setwd(workdir)
	
	regName <- paste0("HF_GEO_",taskId)
	
	reg <- makeHtsflowRegistry(regName)
	
	numjobs <- length(gsms)
	
	ids <- batchMap(reg, fun=downloadGSM, rep(gse, numjobs), gsms, rep(userUploadDir, numjobs))
	submitJobs(reg)
	waitForJobs(reg)
	
	print(paste0("Status of jobs for registry %s: ", regName))
	
	showStatus(reg)
	
	errors<-findErrors(reg)
	
	if (length(errors) > 0) { 
		sqlSampleId <- "SELECT nextSampleId();";
		sampleId <- extractSingleColumnFromDB(sqlSampleId)
		loginfo(paste0("Create new sample: ", sampleId, " for GSMs: ", geoIds))
		
		readLength = 0		
		readMode   = ''
		refGenome  = 'unspecified'
		
		method <- "unspecified"
		library_layout <- "unspecified"
		organism <- "unspecified"
		
		description <- paste0("Data downloaded from GEO: ", geoIds, ". No metadata available, we cannot download this dataset.")
		
		sqlSample <- paste0('INSERT INTO sample (id, sample_name, seq_method, reads_length, reads_mode, ref_genome, raw_data_path, user_id, source, raw_data_path_date) ', 
				"SELECT 'X" , sampleId, "', '" , "GEO SAMPLE NOT AVAILABLE", "','" , method , "','" , 
				readLength , "','" , readMode , "','" ,refGenome  , "','" , userUploadDir, "', user_id, 2, NOW() FROM users WHERE user_name = '" , user , "'")
		
		res <- updateInfoOnDB( sqlSample )
		
		description<-gsub("'", "\\'", description)
		description<-gsub("\\ +\\|\\|\\ +", "\n", description)
		
		sqlDescription <- paste0("INSERT INTO sample_description (sample_id, description) VALUES ('X", sampleId, "', '", description, "')")
		loginfo(sqlDescription)
		res <- updateInfoOnDB( sqlDescription )
		
		SQL <- paste("UPDATE other_analysis SET status='Error', dateEnd=NOW() WHERE id=", taskId ,sep="")
		res <- updateInfoOnDB( SQL )
		
		stop(paste0(length(errors), " job(s) failed, exit"))
	}
	
	SQL <- paste("UPDATE other_analysis SET status='completed', dateEnd=NOW() WHERE id=", taskId ,sep="")
	res <- updateInfoOnDB( SQL )
}

downloadGSM <- function( gse, gsm , userUploadDir) {
	
	initSpecies()
	
	#Connection to the Metadata databases available on the cluster
	# Do we need to connect each time we want to download a GSM/GSE???
	sra_con <- connectToSRADB()
	geo_con <- connectToGEOmetaDB()	
	
	sqlSampleId <- "SELECT nextSampleId();";
	sampleId <- extractSingleColumnFromDB(sqlSampleId)
	loginfo(paste0("Create new sample: ", sampleId, " for GSM ", gse, "/", gsm))
	
	user<-Sys.getenv("USER")
	
	
	#If GSE field is empty or null we have to find the GSE associated to the GSM
	
	if(gse=='' | is.null(gse) | is.na(gse)){
		if(gsm=='' | is.null(gsm) | is.na(gsm)){l
			stop("Invalid GSE and GSM provided")
		}else{
			gse_ids <- dbGetQuery(geo_con, paste0("select distinct gse from gse_gsm where gsm ='", toupper(gsm), "'"))
			loginfo(gse_ids)
			if(nrow(gse_ids)==0){
				loginfo(paste0("No GSE found for id ", gsm))
				gse=''		
			}else{
				gse <- gse_ids[1,1]
			}
		}
	}#If a  GSE has been provided
	else{
		#If a GSM has been provided we have to check that the GSM belongs to the GSE
		
		gsm_ids <-  dbGetQuery(geo_con, paste0("select distinct gsm from gse_gsm where gse ='", toupper(gse), "'"))
		
		if(length(gsm_ids)==0){
			stop(paste0("No GSMs found for id ", gse))
		}
		
		if(gsm=='' | is.null(gsm) | is.na(gsm)) {
			print("Missing GSM")
		} 
		
		if(! toupper(gsm) %in% gsm_ids[,1]) {
			stop(paste0("Wrong match: ", gsm , " does not belong to  " , gse))
		}
		
	}	
	
	
	#Create the appropriate folder structure
	if(!file.exists(userUploadDir)){
		stop("Invalid directory")
	}else{
		
		userUploadDir <- paste0(userUploadDir, '/', gse, '/', gsm)
		if(file.exists(userUploadDir))
			#stop("Directory already existing, probably you have already downloaded the sample")
			loginfo("Directory already existing, probably you have already downloaded the sample")
		if(!file.exists(userUploadDir)){
			dir.create(userUploadDir, recursive=TRUE)
			loginfo('Directory created')
			loginfo(userUploadDir)
		}			
		
		#Querying the database to obtain metadata
		gsm_metadata <- dbGetQuery(sra_con, paste0("select distinct experiment_accession, sample_accession, experiment_alias, library_strategy, library_layout, title from experiment where experiment_url_link like '%", toupper(gsm), "'"))	
		## loginfo("GSM metadata: ")
		## loginfo(gsm_metadata)
		## loginfo("SRA metadata: ")
		## loginfo(sra_metadata)
		
		if(nrow(gsm_metadata)==0) {			
			loginfo(paste0('No metadata found in database for SRAdb for GSM: ' , gsm))	
			stop(paste0('No metadata found in database for SRAdb for GSM: ' , gsm))
		} else if(nrow(gsm_metadata)>1) {
			loginfo('Multiple experiments found')
			stop('Multiple experiments found')
		} else {
			sra_metadata <- dbGetQuery(sra_con, paste0("select distinct taxon_id, scientific_name, sample_attribute from sample where sample_accession=='", gsm_metadata$sample_accession, "'"))[1,]
			method <- tolower(gsm_metadata$library_strategy)
			library_layout <- gsm_metadata$library_layout
			organism <- gsub(" ", "_", sra_metadata[,c(1,2)]$scientific_name) # this is a taxid			
			description <- sra_metadata$sample_attribute			
			
			
			readLength = 0
			
			if (organism %in% names(htsflowSpeciesToVersions)) {
				refGenome <- htsflowSpeciesToVersions[[organism]]
			} else {			
				refGenome <- organism
			}
			
			loginfo(paste0("Genome and version: ", organism, ", ",refGenome))
			
			loginfo(paste0('Downloading:', gsm_metadata$experiment_accession))	
			
			# Check if the file has already been downloaded
			sra_files <- list.files(path=userUploadDir, pattern="*.sra$", full.names=T, recursive=FALSE)
			if (length(sra_files) > 0) {
				loginfo(paste0('An SRA file is already present, skip downloading:'))
			} else {
				downloadSRA(sra_con, userUploadDir, gsm_metadata$experiment_accession)
			}
		
			sraToolkitPath <- ""
			
			
			sra_files <- list.files(path=userUploadDir, pattern="*.sra$", full.names=T, recursive=FALSE)
			
			if(library_layout != '' && grepl('PAIR', library_layout)){
				readMode <- "PE"
				
				## lapply(files, function(sra_file){
				##             callSRAToolkit("/data/BA/tools/sratoolkit.2.2.2a-centos_linux64/bin/fastq-dump --split-3", sra_file)
				##             new_sra_name <- gsub("_1.fastq", "_R1.fastq", sra_file)
				##             new_sra_name <- gsub("_2.fastq", "_R2.fastq", sra_file)
				##             file.rename(sra_file, new_sra_name) #library(files)
				##             gzip(new_sra_name, destname=sprintf("%s.gz", new_sra_name), temporary=FALSE, skip=FALSE,
				##                     overwrite=FALSE, remove=TRUE) # This is in   library(R.utils)
				##         })
				## 
				
				lapply(sra_files, function(sra_file){
							tryOrExit(paste0(getHTSFlowPath('fastqDump'), " --split-3 --outdir ", userUploadDir, " ", sra_file), "GEO")
							sra1FileName <-  gsub(".sra", "_1.fastq", sra_file) 
							sra2FileName <-  gsub(".sra", "_2.fastq", sra_file) 
							
							sra1NewFileName <- gsub(".sra", "_R1.fastq", sra_file) 
							sra2NewFileName <- gsub(".sra", "_R2.fastq", sra_file) 
							
							file.rename( sra1FileName, sra1NewFileName) #library(files)
							file.rename( sra2FileName, sra2NewFileName) #library(files)
							
							gzip(sra1NewFileName, destname=sprintf("%s.gz", sra1NewFileName), temporary=FALSE, skip=FALSE,
									overwrite=FALSE, remove=TRUE)						 
							gzip(sra2NewFileName, destname=sprintf("%s.gz", sra2NewFileName), temporary=FALSE, skip=FALSE,
									overwrite=FALSE, remove=TRUE)
							unlink(sra_file)
							#gzip(sra_file, destname=sprintf("%s.gz", sra_file), temporary=FALSE, skip=FALSE,
							#		overwrite=FALSE, remove=TRUE)
							
						})
				
			}
			else {
				readMode <- "SE"
				lapply(sra_files, function(sra_file){
							tryOrExit(paste0(getHTSFlowPath('fastqDump'), " --outdir ", userUploadDir, " ", sra_file), "GEO")
							#call(getHTSFlowPath('fastqDump'), sra_file)
							fastq_name <- gsub(".sra", ".fastq", sra_file)
							#file.rename(sra_file, new_sra_name)
							gzip(fastq_name, destname=sprintf("%s.gz", fastq_name), temporary=FALSE, skip=FALSE,
									overwrite=FALSE, remove=TRUE) # This is in a library called R.utils
							unlink(sra_file)
						})
				
				
			}
			
			sqlSample <- paste0('INSERT INTO sample (id, sample_name, seq_method, reads_length, reads_mode, ref_genome, raw_data_path, user_id, source, raw_data_path_date) ', 
					"SELECT 'X" , sampleId, "', '" , gsm , "','" , method , "','" , 
					readLength , "','" , readMode , "','" ,refGenome  , "','" , userUploadDir, "', user_id, 2, NOW() FROM users WHERE user_name = '" , user , "'")
			
			
			res <- updateInfoOnDB( sqlSample )
			
			description<-gsub("'", "\\'", description)
			description<-gsub("\\ +\\|\\|\\ +", "\n", description)
			
			sqlDescription <- paste0("INSERT INTO sample_description (sample_id, description) VALUES ('X", sampleId, "', '", description, "')")
			loginfo(sqlDescription)
			res <- updateInfoOnDB( sqlDescription )
			
			
			loginfo(paste0("Create new sample: ", sampleId, " for GSM ", gse, "/", gsm, "added in directory ", userUploadDir))
		}
	}
}



