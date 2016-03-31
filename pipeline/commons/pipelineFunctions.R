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
library(logging)

getUserDir <- function() {
	user<-Sys.getenv("USER")
	userdirs<-getHTSFlowPath("HTSFLOW_USERS")	
	return (paste0(userdirs, "/", user))	
}

# Workdir is a sub folder of the user directory in the HTS flow output
setUserWorkDir <- function() {
		
	id <- get("PIPELINE_ID", envir=globalenv())
	type <- get("PIPELINE_TYPE", envir=globalenv())
	
	workdir<-paste0(getUserDir(), "/", getJobID(id, type))
	
	loginfo(paste("set Workdir: " , workdir))
	if (! file.exists(workdir)) {
		createDir(workdir, recursive=TRUE)
	}
	setwd(workdir)
}

getJobID <- function(id, type) {
	if (type == "primary") {
		prefix<-"P"
	} else if (type == "secondary") {
		prefix<-"S"
	} else if (type == "peak_calling") {
		prefix<-"K"
	} else  if (type == "merging") {
		prefix = "M"
	} else  if (type == "other") {
		prefix<-"O"
	} else {
		loginfo("unknown type: %s", type)
		prefix<-"U"
	}
	
	id<-paste0(prefix, id)
	return(id)
}

getPreprocessDir <- function() {
	
	id <- get("PIPELINE_ID", envir=globalenv())
	type <- get("PIPELINE_TYPE", envir=globalenv())	
	
	if (type == "primary") {
		prefix<-"P"
	} else if (type == "secondary") {
		prefix<-"S"
	} else if (type == "peak_calling") {
		prefix<-"K"
	} else  if (type == "merging") {
		prefix = "M"
	} else  if (type == "other") {
		prefix<-"O"
	} else {
		loginfo("unknown type: %s", type)
		prefix<-"O"
	}
	
	preprocessDir <- paste0(getHTSFlowPath("HTSFLOW_PREPROCESS"), "/", prefix, id, "/")
		
	return(preprocessDir)
}


getGenomePaths <- function ( genomeName = "mm9" ) {
	listNames <- c(
			"genome"
			,"genome_bs"
			,"ribosomial"
			,"genes"
			,"transcripts"
			,"chromSize"
			,"chromSize_bs"
			,"transcripts-index"
			,"fasta"
	)
	## GenPaths <- c(
	##         paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"_bs/",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,"_rib",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/","GTF/","genes.gtf",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/","GTF/","transcripts.gtf",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,".chrom.sizes",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"_bs/",genomeName,".chrom.sizes",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/","transcriptome_data/transcripts",sep="")
	##         ,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,".fa",sep="")
	## )
	
	
	# Moved BS genomes to the main directory
	GenPaths <- c(
			paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,"_rib",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/","GTF/","genes.gtf",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/","GTF/","transcripts.gtf",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,".chrom.sizes",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,".chrom.sizes",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/","transcriptome_data/transcripts",sep="")
			,paste( getHTSFlowPath("HTSFLOW_GENOMES"),"/",genomeName,"/",genomeName,".fa",sep="")
	)
	
	df <- data.frame(
			path=GenPaths
			,stringsAsFactors=F
	)
	rownames(df) <- listNames
	return (df)
}

removeReadsFromBAM <- function( BAMin, BAMout, FinalPercentage ) {
	loginfo("Remove from BAM")
	execute <- paste(
			getHTSFlowPath("samtools")
			,'view -h'
			,BAMin
			,'| perl'
			,getHTSFlowPath("randomLine")
			,signif(FinalPercentage, 5)
			,'|'
			,getHTSFlowPath("samtools")
			,'view -bS - >'
			,BAMout
	)
	loginfo(execute)
	result <- system(execute)
	if (result > 0) {
		return(result)
	}
	execute <- paste0( getHTSFlowPath("samtools"), " view -c ", BAMout )
	loginfo(execute)
	readsNum <- as.numeric( system( execute, intern=T ) )
	loginfo( paste0( BAMout, " : ",readsNum ) )
	return(readsNum)
}


downsampled <- function( CHIP_ID, IDsec_FOLDER, BAMfolder=paste0(getHTSFlowPath("HTSFLOW_ALN"), '/'), toDo='create' ) { #paths=HTSFLOW_Path(),
	
	loginfo("Start downsampling")
	
	CHIP_BAM <- paste0( BAMfolder, CHIP_ID, ".bam" )
	loginfo(paste("ChIP BAM: ", CHIP_BAM))
	CHIP_BAM_80 <- paste0( IDsec_FOLDER, CHIP_ID, "_80.bam" )
	CHIP_BAM_60 <- paste0( IDsec_FOLDER, CHIP_ID, "_60.bam" )
	CHIP_BAM_40 <- paste0( IDsec_FOLDER, CHIP_ID, "_40.bam" )
	CHIP_BAM_20 <- paste0( IDsec_FOLDER, CHIP_ID, "_20.bam" )
	
	# check if bam are missing
	# downsampling BAM
	if (toDo == 'create') {
		loginfo("Launch jobs: downsampling")
		batch_chip2 <- c(CHIP_BAM_80,CHIP_BAM_60,CHIP_BAM_40,CHIP_BAM_20)
		batch_val <- c(0.8,0.6,0.4,0.2)
		loginfo("create registry : downsampling")
		
		# load config and common functions
		workdir <- getwd()
		setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
		
		## library("BatchJobs", quietly = TRUE)
		loadConfig("BatchJobs.R")
		setwd(workdir)
				
		regName <- paste0("HF_DS",CHIP_ID)
		reg <- makeHtsflowRegistry(regName)
		
		ids <- batchMap(reg, fun=removeReadsFromBAM, rep(CHIP_BAM, 4), batch_chip2, batch_val)
		submitJobs(reg)
		
		waitForJobs(reg)
		
		showStatus(reg)
		
		errors<-findErrors(reg)
		
		if (length(errors) > 0) {
			#removeRegistry(reg,"no")
			stop(paste0(length(errors), " job(s) failed, exit"))
		}
		
		#removeRegistry(reg,"no")		
	}
	
	if (toDo == 'remove'){
		deleteFile(CHIP_BAM_80, recursive=TRUE)
		deleteFile(CHIP_BAM_60, recursive=TRUE)
		deleteFile(CHIP_BAM_40, recursive=TRUE)
		deleteFile(CHIP_BAM_20, recursive=TRUE)
	}
}

getNumberOfGenes <- function(assembly) {
	genomePath <- paste(getHTSFlowPath("HTSFLOW_GENOMES"),"/",assembly,"/","GTF/","genes.gtf",sep="")
	execute <- paste0( "grep -v -P '^ERCC' ", genomePath, " |cut -f 9  | sort -u | wc -l" )	
	readsNum <- as.numeric( system( execute, intern=T ) )
	loginfo(paste0( "number of known genes for assembly ", assembly, ": ", readsNum))
	return(readsNum)
}

checkMixPresence <- function( values ) {	
	if ( all ( is.null( values$mix_spike )) || all ( is.na( values$mix_spike ))  || all ( "NULL" == values$mix_spike )  || all ( "" == values$mix_spike ) ){
		return (0)
	} else {
		return (1)
	}
}


