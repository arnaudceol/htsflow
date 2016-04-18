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

# load configuration
options(scipen=999)

library(logging, quietly = TRUE)
library("BatchJobs", quietly = TRUE)

peakCallingJob <- function( IDsec, IDpeak, peak_calling=TRUE, annotation=TRUE ) {
	basicConfig()
	
	
	
	execute <- paste( "SELECT * FROM peak_calling WHERE id=", IDpeak, ";" )
	IDpreList <- extractInfoFromDB( execute )
	
	typeOfpeakCalling <- IDpreList$program
	
	INPUT_ID <- IDpreList$input_id
	CHIP_ID <- IDpreList$primary_id
	label <- IDpreList$label
	pval <- IDpreList$pvalue
	IDsec <- IDpreList$secondary_id
	
	if ( 1 == IDpreList$saturation) {
		saturation <- TRUE
		loginfo("Perform saturation.")
	} else {
		saturation <- FALSE
		loginfo("Skip saturation.")
	}
	
	stats_opt <- IDpreList$stats #options for macs2 calling
	
	REFGENOME <- unique( unlist( sapply( IDpreList$primary_id, function(x) extractInfoFromDB( paste0('SELECT ref_genome FROM sample, primary_analysis WHERE sample.id = sample_id AND source = origin AND primary_analysis.id=',x ) ) ) ) )
	genomePaths <- getGenomePaths( REFGENOME )
	
	loginfo(paste("Ref genome:", REFGENOME))
	
	IDsec_FOLDER <- paste0 ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/" )
	BAMfolder <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"), "/" )
	
	SQL <- paste( "SELECT reads_num FROM primary_analysis WHERE id=", INPUT_ID, ";" )
	INPUTReads <- as.numeric( extractSingleColumnFromDB( SQL ) )
	SQL <- paste( "SELECT reads_num FROM primary_analysis WHERE id=", CHIP_ID, ";" )
	CHIPReads <- as.numeric( extractSingleColumnFromDB( SQL ) )
	
	loginfo(paste("ChIP/input reads: ", CHIPReads, INPUTReads))
	
	fileBEDnarrow <- paste0( IDsec_FOLDER, "NARROW", "/", label, "_peaks.bed" )
	fileBEDbroad <- paste0( IDsec_FOLDER, "BROAD", "/", label, "_broad_peaks.bed" )
	
	bedFolder <- paste0(getHTSFlowPath("HTSFLOW_BED"), '/', IDsec, '/bed/' )
	createDir(bedFolder,  recursive =  TRUE)		

	

	###### peakcaller function #######
	if ( peak_calling ) {
		
		# prepare downsample files
		if (saturation) {
			loginfo("Downsampling ChIP ID: %s",  CHIP_ID)
			downsampled( CHIP_ID, IDsec_FOLDER, BAMfolder, "create" ) # this function is in pipeFunctions.R
		}
		
		if ( typeOfpeakCalling == "MACSboth" ) {
			pvalue <- unlist( strsplit( pval, split=';' ) )
			stats <- unlist( strsplit( stats_opt, split=';' ) )
			macsOUT1 <- paste0( IDsec_FOLDER, 'NARROW/' )
			macsOUT2 <- paste0( IDsec_FOLDER, 'BROAD/' )
			
			# Send broad in parallel and run narrow
			
			
			## batchPvalue <-  c(pvalue[1], pvalue[2])
			## batchStats <-  c(stats[1], stats[2])
			## batchMacsOut <-  c(macsOUT1, macsOUT2)
			## batchType <- c("MACSnarrow","MACSbroad")
			
			regName <- paste0("HF_BROAD",IDpeak)
			
			# load config and common functions
			workdir <- getwd()
			setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
			
			loadConfig("BatchJobs.R")
			
			## # Ensure we use tha maximum of jobs alowed.
			## config <-getConfig()
			## config["max.concurrent.jobs"] <- "Inf"
			## setConfig(config)
			## 
			## loginfo(getConfig())
			
			
			setwd(workdir)
						
			reg <- makeHtsflowRegistry(regName)
			ids <- batchMap(reg, fun=peakcaller, INPUT_ID, CHIP_ID, label,pvalue[2], stats[2],
					IDsec_FOLDER, BAMfolder, macsOUT2 , REFGENOME,	saturation, "MACSbroad")
			loginfo("Submit broad peak calling as a new job.")
			submitJobs(reg)
			showStatus(reg)
			
			loginfo("Broad peals launched in parallel, continue with narrow peaks")
			
			# Run narrow and then wait for broad to finish
			peakcaller(INPUT_ID, CHIP_ID, label,pvalue[1], stats[1],
			IDsec_FOLDER, BAMfolder, macsOUT1 , REFGENOME,saturation, "MACSnarrow")
				
			waitForJobs(reg)
			
			loginfo(paste0("Status of jobs for registry %s: ", regName))
			
			showStatus(reg)
			
			errors<-findErrors(reg)
			
			if (length(errors) > 0) {
				stop(paste0(length(errors), " job(s) failed, exit"))
			}
							
			# GR -> bedfile
			grCHIP1 <- loadGR( fileBEDnarrow, typeOfpeakCalling )
			grCHIP2 <- loadGR( fileBEDbroad, typeOfpeakCalling )
			grCHIP <- union( grCHIP1, grCHIP2 )

			# create dir for BOTH
			macsOUTboth <- paste0( IDsec_FOLDER, 'BOTH/' )
			if (! file.exists(macsOUTboth)){
				loginfo(paste("create directory: ", macsOUTboth))
				createDir(macsOUTboth, recursive=TRUE)		
			}

			fileBEDboth <- paste0( macsOUTboth, label, "_peaks.bed" )
			write.table(as.data.frame(grCHIP)[,1:4], fileBEDboth, quote=FALSE, sep="\t", row.names=FALSE, col.names=FALSE)
			
			makeBigBedFile( fileBEDnarrow, genomePaths, bedFolder )
			makeBigBedFile( fileBEDbroad, genomePaths, bedFolder )
			makeBigBedFile( fileBEDboth, genomePaths, bedFolder )
			
		} else if ( typeOfpeakCalling == "MACSnarrow" ) {
			macsOUT <- paste0( IDsec_FOLDER, 'NARROW/' )
			peakcaller( INPUT_ID, CHIP_ID, label, pval, stats_opt, IDsec_FOLDER, BAMfolder, macsOUT, REFGENOME, saturation, typeOfpeakCalling )
			makeBigBedFile( fileBEDnarrow, genomePaths, bedFolder )			
		} else if ( typeOfpeakCalling == "MACSbroad" ) {
			macsOUT <- paste0( IDsec_FOLDER, 'BROAD/' )
			peakcaller( INPUT_ID, CHIP_ID, label, pval, stats_opt, IDsec_FOLDER, BAMfolder, macsOUT, REFGENOME, saturation, typeOfpeakCalling )
			makeBigBedFile( fileBEDbroad, genomePaths, bedFolder )
		}
		
		
		if (saturation) {
			loginfo("Remove downsampling files for ChIP ID: %s",  CHIP_ID)			
			downsampled( CHIP_ID, IDsec_FOLDER, BAMfolder,  'remove' )
		}
		
	}
	###### annotation function #######
	if ( annotation ) {
		loginfo("Annotating")
		annFolder <- paste0( IDsec_FOLDER, "annotation/" )
		createDir(annFolder,  recursive =  TRUE)	
		
		grCHIP <- annotateGR( INPUT_ID, CHIP_ID, label, IDsec_FOLDER, typeOfpeakCalling, BAMfolder, REFGENOME )
		
		# write output
		loginfo ("Writing outputs")
		setwd( annFolder )
		saveRDS( grCHIP, file = paste0( label, ".rds" ) )
	} else {
		if ( typeOfpeakCalling == 'MACSnarrow' ) {
			grCHIP <- loadGR( fileBEDnarrow, typeOfpeakCalling )
		}
		if ( typeOfpeakCalling == 'MACSbroad' ) {
			grCHIP <- loadGR( fileBEDbroad, typeOfpeakCalling )
		}
		if ( typeOfpeakCalling == 'MACSboth' ) {
			grCHIP1 <- loadGR( fileBEDnarrow, typeOfpeakCalling )
			grCHIP2 <- loadGR( fileBEDbroad, typeOfpeakCalling )
			grCHIP <- union( grCHIP1, grCHIP2 )
		}
		saveRDS( grCHIP, file = paste0( label, ".rds" ) )
	}
	
	loginfo ("END PEAK CALLING")
}


peakCalling <- function( IDsec ){
	method = "peak_calling"

	loginfo (paste("Peak Calling Analysis: ", IDsec))
	
	values <- getSecondaryData(IDsec, method)	
	
	outFolder <- paste ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/", sep="" )
	
	createDir(outFolder,  recursive =  TRUE)	
	
	BAMfolder <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"), "/" )
	typeOfpeakCalling <- values[1,]$program
	
	loginfo("Type of peak calling: %s", typeOfpeakCalling)
	loginfo("Number of ids: %s", length(values$primary_id))
	
	execute <- paste( "SELECT DISTINCT saturation, program FROM peak_calling, secondary_analysis WHERE secondary_id = secondary_analysis.id AND secondary_analysis.id=", IDsec, ";" )
	saturationAndProgram <- extractInfoFromDB( execute )
	
	
	# load config and common functions
	workdir <- getwd()
	setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
	
	loadConfig("BatchJobs.R")
	setwd(workdir)
	
	
	regName <- paste0("HF_PC",IDsec)
	
	reg <- makeHtsflowRegistry(regName)	
	ids <- batchMap(reg, fun=peakCallingJob, IDsec=values[,"secondary_id"], IDpeak=values[,"id"])
	
	
	# How many jobs will be launched?
	numPeaks = length(values[,"id"])
	
	saturation = saturationAndProgram$saturation
	typeOfpeakCalling = saturationAndProgram$program
	
	numJobsPerPeak <- 1
	
	if ( 1 == saturation) {
		if ( typeOfpeakCalling == "MACSboth" ) {
			numJobsPerPeak <- 10
		} else {
			numJobsPerPeak <- 5		
		}		
	} else {		
		if ( typeOfpeakCalling == "MACSboth" ) {
			numJobsPerPeak <- 2
		} else {
			numJobsPerPeak <- 1
		}		
	}
	
	maxConcurentJobsAllowed = as.numeric(getHTSFlowPath("max_jobs"))
	if (!is.na(maxConcurentJobsAllowed) & ! maxConcurentJobsAllowed == "Inf") {
		# Set max number of concurent jobs, to ensure we do not block everything
		numChunks = floor(maxConcurentJobsAllowed / numJobsPerPeak)
		loginfo(paste0("Limit the number of concurent peaks to ", numChunks, " chunks."))			
		chunked = chunk(getJobIds(reg), n.chunks = numChunks, shuffle = FALSE)
		submitJobs(reg, chunked)
	} else {				
		submitJobs(reg)				
	}
	
	
	
	submitJobs(reg)
	
	waitForJobs(reg)
	
	loginfo(paste0("Status of jobs for registry: ", regName))
	showStatus(reg)
	
	errors<-findErrors(reg)
	
	if (length(errors) > 0) {
		## #removeRegistry(reg,"no")
		stop(paste0(length(errors), " job(s) failed, exit"))
	}
		
	#removeRegistry(reg,"no")
	
	## setSecondaryStatus( IDsec=IDsec, status='Calling peaks..' )
	## 
	## for ( i in 1:length(values$primary_id) ) {
	##     if ( 1 == IDpreList$saturation) {
	##         CHIP_ID <- values$primary_id[i]
	##         downsampled( CHIP_ID, outFolder, BAMfolder,  'remove' )
	##     } else {
	##         loginfo("No saturation: skip downsampling")
	##     }	
	## }
	## 
	setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
}


