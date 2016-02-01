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

peakCallingJob <- function( IDsec, IDpeak, peak_calling=TRUE, saturation=TRUE, annotation=TRUE ) {
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
	}else {
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
	
	GenomeBrowserFolder <- paste0( IDsec_FOLDER, '/bed/' )
	createDir(GenomeBrowserFolder,  recursive =  TRUE)		
	
	###### peakcaller function #######
	if ( peak_calling ) {
		if ( typeOfpeakCalling == "MACSboth" ) {
			pvalue <- unlist( strsplit( pval, split=';' ) )
			stats <- unlist( strsplit( stats_opt, split=';' ) )
			macsOUT1 <- paste0( IDsec_FOLDER, 'NARROW/' )
			macsOUT2 <- paste0( IDsec_FOLDER, 'BROAD/' )
			
			batchPvalue <-  c(pvalue[1], pvalue[2])
			batchStats <-  c(stats[1], stats[2])
			batchMacsOut <-  c(macsOUT1, macsOUT2)
			batchType <- c("MACSnarrow","MACSbroad")
			
			regName <- paste0("HF_PC_sub",IDpeak)
			
			# load config and common functions
			workdir <- getwd()
			setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
			
			loadConfig("BatchJobs.R")
			setwd(workdir)
			
			
			reg <- makeHtsflowRegistry(regName)
			ids <- batchMap(reg, fun=peakcaller, rep(INPUT_ID, 2), rep(CHIP_ID,2), rep(label, 2),batchPvalue, batchStats,
					rep(IDsec_FOLDER, 2),
					rep(BAMfolder, 2), batchMacsOut,rep(REFGENOME, 2),
					rep(saturation, 2), batchType)
			submitJobs(reg)
			waitForJobs(reg)
			
			loginfo(paste0("Status of jobs for registry %s: ", regName))
			
			showStatus(reg)
			
			errors<-findErrors(reg)
			
			if (length(errors) > 0) {
				stop(paste0(length(errors), " job(s) failed, exit"))
			}
			
			
			makeBigBedFile( fileBEDnarrow, genomePaths, GenomeBrowserFolder )
			makeBigBedFile( fileBEDbroad, genomePaths, GenomeBrowserFolder )
			
		} else if ( typeOfpeakCalling == "MACSnarrow" ) {
			macsOUT <- paste0( IDsec_FOLDER, 'NARROW/' )
			peakcaller( INPUT_ID, CHIP_ID, label, pval, stats_opt, IDsec_FOLDER, BAMfolder, macsOUT, REFGENOME, saturation, typeOfpeakCalling )
			makeBigBedFile( fileBEDnarrow, genomePaths, GenomeBrowserFolder )			
		} else if ( typeOfpeakCalling == "MACSbroad" ) {
			macsOUT <- paste0( IDsec_FOLDER, 'BROAD/' )
			peakcaller( INPUT_ID, CHIP_ID, label, pval, stats_opt, IDsec_FOLDER, BAMfolder, macsOUT, REFGENOME, saturation, typeOfpeakCalling )
			makeBigBedFile( fileBEDbroad, genomePaths, GenomeBrowserFolder )
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
	
	execute <- paste( "SELECT saturation FROM peak_calling, secondary_analysis WHERE secondary_id = secondary_analysis.id AND secondary_analysis.id=", IDsec, ";" )
	IDpreList <- extractInfoFromDB( execute )
	
	
	for ( i in 1:length(values$primary_id) ) {
		if ( 1 == IDpreList$saturation) {	
			loginfo("Get saturation")			
			# update the DB with the secondary analysis status complete
			setSecondaryStatus( IDsec=IDsec, status='Downsampling..', startTime=T )
			CHIP_ID <- values$primary_id[i]
			loginfo("ChIP ID: %s",  CHIP_ID)
			downsampled( CHIP_ID, outFolder, BAMfolder, "create" ) # this function is in pipeFunctions.R
		} else {
			loginfo("No saturation: skip downsampling")
		}
	}
	
	loginfo("Start peak calling after saturation")
		
	# load config and common functions
	workdir <- getwd()
	setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
	
	loadConfig("BatchJobs.R")
	setwd(workdir)
	
	regName <- paste0("HF_PC",IDsec)
	
	reg <- makeHtsflowRegistry(regName)	

	ids <- batchMap(reg, fun=peakCallingJob, IDsec=values[,"secondary_id"], IDpeak=values[,"id"])
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
	
	setSecondaryStatus( IDsec=IDsec, status='Calling peaks..' )
	
	for ( i in 1:length(values$primary_id) ) {
		if ( 1 == IDpreList$saturation) {
			CHIP_ID <- values$primary_id[i]
			downsampled( CHIP_ID, outFolder, BAMfolder,  'remove' )
		} else {
			loginfo("No saturation: skip downsampling")
		}	
	}
	
	setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
}


