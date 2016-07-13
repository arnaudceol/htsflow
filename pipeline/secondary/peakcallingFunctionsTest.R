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

create_saturation_file <- function( label, macsOUT ) {
	# GENERATING THE FINAL TABLE FOR SATURATION ANALYSIS
	label20 <- paste0(label,"_20")
	label40 <- paste0(label,"_40")
	label60 <- paste0(label,"_60")
	label80 <- paste0(label,"_80")
	
	CHIP_FILE20 <- paste0( macsOUT, label20, "_peaks.xls" )
	CHIP_FILE40 <- paste0( macsOUT, label40, "_peaks.xls" )
	CHIP_FILE60 <- paste0( macsOUT, label60, "_peaks.xls" )
	CHIP_FILE80 <- paste0( macsOUT, label80, "_peaks.xls" )
	CHIP_FILE100 <- paste0( macsOUT, label, "_peaks.xls" )
	
	SaturationMatrix <- as.data.frame( t ( as.matrix( sapply( 1:length(seq(2,100,2)), function(x) { c( seq(2,100,2)[x],seq(2,100,2)[x+1]  )  } ) ) ) )
	colnames(SaturationMatrix) <- c("range1","range2")
	SaturationMatrix <- SaturationMatrix[1:dim(SaturationMatrix)[1]-1,]
	
	if (file.exists(CHIP_FILE20)) {
		tmp20 <- read.table( CHIP_FILE20, header=T )
		l20 <- sapply( 1:dim(SaturationMatrix)[1], function(x) {
					R1_20 <- SaturationMatrix[x,]$range1
					R2_20 <- SaturationMatrix[x,]$range2
					dim( tmp20[ tmp20$fold_enrichment > R1_20 & tmp20$fold_enrichment <= R2_20, ] )[1]
				})
	} else {
		l20 <- rep(0, dim(SaturationMatrix)[1])
	}
	
	if (file.exists(CHIP_FILE40)) {
		tmp40 <- read.table( CHIP_FILE40, header=T )
		l40 <- sapply( 1:dim(SaturationMatrix)[1], function(x) {
					R1_40 <- SaturationMatrix[x,]$range1
					R2_40 <- SaturationMatrix[x,]$range2
					dim( tmp40[ tmp40$fold_enrichment > R1_40 & tmp40$fold_enrichment <= R2_40, ] )[1]
				})
	} else {
		l40 <- rep(0, dim(SaturationMatrix)[1])
	}
	
	if (file.exists(CHIP_FILE60)) {
		tmp60 <- read.table( CHIP_FILE60, header=T )
		l60 <- sapply( 1:dim(SaturationMatrix)[1], function(x) {
					R1_60 <- SaturationMatrix[x,]$range1
					R2_60 <- SaturationMatrix[x,]$range2
					dim( tmp60[ tmp60$fold_enrichment > R1_60 & tmp60$fold_enrichment <= R2_60, ] )[1]
				})
	} else {
		l60 <- rep(0, dim(SaturationMatrix)[1])
	}
	
	if (file.exists(CHIP_FILE80)) {
		tmp80 <- read.table( CHIP_FILE80, header=T )
		l80 <- sapply( 1:dim(SaturationMatrix)[1], function(x) {
					R1_80 <- SaturationMatrix[x,]$range1
					R2_80 <- SaturationMatrix[x,]$range2
					dim( tmp80[ tmp80$fold_enrichment > R1_80 & tmp80$fold_enrichment <= R2_80, ] )[1]
				})
	} else {
		l80 <- rep(0, dim(SaturationMatrix)[1])
	}
	
	if (file.exists(CHIP_FILE100)) {
		tmp100 <- read.table( CHIP_FILE100, header=T )
		l100 <- sapply( 1:dim(SaturationMatrix)[1], function(x) {
					R1_100 <- SaturationMatrix[x,]$range1
					R2_100 <- SaturationMatrix[x,]$range2
					dim( tmp100[ tmp100$fold_enrichment > R1_100 & tmp100$fold_enrichment <= R2_100, ] )[1]
				})
	} else {
		l100 <- rep(0, dim(SaturationMatrix)[1])
	}
	
	SaturationMatrix <- cbind( SaturationMatrix, l100, l80, l60, l40, l20)
	OutMatrix <- data.frame( "reads100"=SaturationMatrix$l100, "reads80"=SaturationMatrix$l80, "reads60"=SaturationMatrix$l60, "reads40"=SaturationMatrix$l40, "reads20"=SaturationMatrix$l20 )
	rownames(OutMatrix) <- sapply( 1:dim(SaturationMatrix)[1], function(x) paste0(SaturationMatrix[x,]$range1,"_",SaturationMatrix[x,]$range2) )
	saturationOUTfile <- paste0( macsOUT, label, "_saturation.txt" )
	loginfo ( paste0 ( "Saving output saturation in: ", saturationOUTfile ) )
	write.table( OutMatrix, file=saturationOUTfile )
}

loadGR <- function( FILE, typeOfpeakCalling ){
	if (typeOfpeakCalling=="MACSbroad") {
		TempGR <- read.table( FILE, sep="\t", skip=1)
		head(TempGR)
	} else {
		TempGR <- read.table( FILE, sep="\t")
		head(TempGR)
	}
	peaksGR <- GRanges(Rle(as.character(TempGR$V1)),IRanges(TempGR$V2, TempGR$V3))	
	return(peaksGR)
}

loadGRBind <- function( FILE, BAM, BAMREF, typeOfpeakCalling ){
	if (typeOfpeakCalling=="MACSbroad") {
		TempGR <- read.table( FILE, sep="\t", skip=1)
	} else {
		TempGR <- read.table( FILE, sep="\t")
	}
	peaksGR <- GRanges(Rle(as.character(TempGR$V1)),IRanges(TempGR$V2, TempGR$V3))
	
	if (BAM==BAMREF) {
		bind <- GRcoverage(peaksGR, BAM)
		peaksGR$coverage <- bind
	} else {
		bind <- GRenrichment(peaksGR, BAM, BAMREF)
		peaksGR$enrichment <- bind
	}
		
	return(peaksGR)
}

loadGRBindBOTH <- function( FILEnarrow, FILEbroad,  BAM, BAMREF ){
	TempGRnarrow <- read.table( FILEnarrow, sep="\t")
	TempGRbroad <- read.table( FILEbroad, sep="\t", skip=1)
	peaksGRnarrow <- GRanges(Rle(as.character(TempGRnarrow$V1)),IRanges(TempGRnarrow$V2, TempGRnarrow$V3))
	peaksGRbroad <- GRanges(Rle(as.character(TempGRbroad$V1)),IRanges(TempGRbroad$V2, TempGRbroad$V3))
	
	peaksGR <- GenomicRanges::union( peaksGRnarrow, peaksGRbroad )
	
	if (BAM==BAMREF) {
		bind <- GRcoverage(peaksGR, BAM)
		peaksGR$coverage <- bind
	} else {
		bind <- GRenrichment(peaksGR, BAM, BAMREF)
		peaksGR$enrichment <- bind
	}
	return(peaksGR)
}

annotateGR <- function( INPUT_ID, CHIP_ID, label, IDsec_FOLDER, typeOfpeakCalling, BAMfolder, REFGENOME ) {
	loginfo(paste("Ref genome: " ,REFGENOME))
	
	annotationLibraryName <- getAnnotationLibraryName(REFGENOME)
	txdbLibraryName <- getTxdbLibraryName(REFGENOME)
	
	library(annotationLibraryName, character.only = TRUE)
	library(txdbLibraryName, character.only = TRUE)
	
	txdb <- get(txdbLibraryName)
	orgdb <- get(annotationLibraryName)
	
	e<-baseenv()
	e$orgdb <- orgdb
	
	loginfo(paste("Ref genome and libraries: ",REFGENOME,annotationLibraryName, txdbLibraryName, sep=", "))
	
	## library( org.Mm.eg.db , quietly = TRUE)
	## library( TxDb.Mmusculus.UCSC.mm9.knownGene)
	## txdb <- TxDb.Mmusculus.UCSC.mm9.knownGene
	
	## if ( REFGENOME == 'mm9' ) {		
	##     library( org.Mm.eg.db , quietly = TRUE)
	##     library( TxDb.Mmusculus.UCSC.mm9.knownGene)
	##     txdb <- TxDb.Mmusculus.UCSC.mm9.knownGene
	## }
	## if ( REFGENOME == 'hg19' ) {
	##     library( org.Hs.eg.db , quietly = TRUE)
	##     library( TxDb.Hsapiens.UCSC.hg19.knownGene , quietly = TRUE)
	##     txdb <- TxDb.Hsapiens.UCSC.hg19.knownGene
	## }
	## if ( REFGENOME == 'mm10' ) {
	##     library( org.Mm.eg.db , quietly = TRUE)
	##     library( TxDb.Mmusculus.UCSC.mm10.knownGene , quietly = TRUE)
	##     txdb <- TxDb.Mmusculus.UCSC.mm10.knownGene
	## }
	## if ( REFGENOME == 'hg18' ) {
	##     library( org.Hs.eg.db , quietly = TRUE)
	##     library( TxDb.Hsapiens.UCSC.hg18.knownGene , quietly = TRUE)
	##     txdb <- TxDb.Hsapiens.UCSC.hg18.knownGene
	## }
	## if ( REFGENOME == 'dm6' ) {
	##     library( org.Dm.eg.db , quietly = TRUE)
	##     txdb <- loadDb('/home/egaleota/txdb.sqlite')
	## }
	## if ( REFGENOME == 'rn5' ) {
	##     library( org.Rn.eg.db , quietly = TRUE)
	##     library( TxDb.Rnorvegicus.UCSC.rn5.refGene , quietly = TRUE)
	##     txdb <- TxDb.Rnorvegicus.UCSC.rn5.refGene
	## }
		
	
	
	fileBEDnarrow <- paste0( IDsec_FOLDER, "NARROW", "/", label, "_narrow_peaks.bed" )
	fileBEDbroad <- paste0( IDsec_FOLDER, "BROAD", "/", label, "_broad_peaks.bed" )
	fileBEDboth <- paste0( IDsec_FOLDER, "BROAD", "/", label, "_both_peaks.bed" )
	
	if ( typeOfpeakCalling == "MACSnarrow" ){
		#fileTMP <- paste0( IDsec_FOLDER, "NARROW", "/", label, "_peaks.bed" )
		GR <- loadGRBind( fileBEDnarrow, paste0( BAMfolder,CHIP_ID,".bam" ), paste0(BAMfolder,INPUT_ID,".bam"), typeOfpeakCalling )
	} else if ( typeOfpeakCalling == "MACSbroad" ){
		#fileTMP <- paste0( IDsec_FOLDER, "BROAD", "/", label, "_peaks.bed" )
		GR <- loadGRBind( fileBEDbroad, paste0( BAMfolder,CHIP_ID,".bam" ), paste0(BAMfolder,INPUT_ID,".bam"), typeOfpeakCalling )
	}  else if ( typeOfpeakCalling == "MACSboth" ){
		#fileTMPnarrow <- paste0( IDsec_FOLDER, "NARROW", "/", label, "_peaks.bed" )
		#fileTMPbroad <- paste0( IDsec_FOLDER, "BROAD", "/", label, "_peaks.bed" )
		GR <- loadGRBindBOTH( fileBEDnarrow, fileBEDbroad,  paste0( BAMfolder,CHIP_ID,".bam" ), paste0(BAMfolder,INPUT_ID,".bam") )
	}
	
	summit <- GRcoverageSummit(GR, paste0( BAMfolder, CHIP_ID, ".bam" ))
	GR$summit <- start(summit)
	GR$midpoint <- start(GRmidpoint(GR))
	
	# annotation differs. use summit for narrow peaks as viewpoint, while midpoint for broad peaks
	loginfo("annotate")
#       loginfo(orgdb)
	
	if ( typeOfpeakCalling == 'MACSnarrow' ) {
		GRtmp <- GR
		start(GRtmp) <- GR$summit
		end(GRtmp) <- GR$summit
		loginfo("Is orgdb a OrgDb?")
		loginfo(is(orgdb,"OrgDb"))
#		GRtmp$orgdb <- orgdb
		loginfo("get annotaions")
		res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=orgdb, upstream=2000, downstream=1000 )
	} else {
		res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=orgdb, upstream=2000, downstream=1000 )
	}
	
	

	## if ( REFGENOME == 'mm9' ) {
	##     # annotation differs. use summit for narrow peaks as viewpoint, while midpoint for broad peaks
	##     if ( typeOfpeakCalling == 'MACSnarrow' ) {
	##         GRtmp <- GR
	##         start(GRtmp) <- GR$summit
	##         end(GRtmp) <- GR$summit
	##         res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=org.Mm.eg.db, upstream=2000, downstream=1000 )
	##     } else {
	##         res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=org.Mm.eg.db, upstream=2000, downstream=1000 )
	##     }
	## }
	## if ( REFGENOME == 'hg19' ) {
	##     if ( typeOfpeakCalling == 'MACSnarrow' ) {
	##         GRtmp <- GR
	##         start(GRtmp) <- GR$summit
	##         end(GRtmp) <- GR$summit
	##         res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=org.Hs.eg.db, upstream=2000, downstream=1000 )
	##     } else {
	##         res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=org.Hs.eg.db, upstream=2000, downstream=1000 )
	##     }
	## 
	## }
	## if ( REFGENOME == 'mm10' ) {
	##     if ( typeOfpeakCalling == 'MACSnarrow' ) {
	##         GRtmp <- GR
	##         start(GRtmp) <- GR$summit
	##         end(GRtmp) <- GR$summit
	##         res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=org.Mm.eg.db, upstream=2000, downstream=1000 )
	##     } else {
	##         res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=org.Mm.eg.db, upstream=2000, downstream=1000 )
	##     }
	## 
	## }
	## if ( REFGENOME == 'hg18' ) {
	##     if ( typeOfpeakCalling == 'MACSnarrow' ) {
	##         GRtmp <- GR
	##         start(GRtmp) <- GR$summit
	##         end(GRtmp) <- GR$summit
	##         res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=org.Hs.eg.db, upstream=2000, downstream=1000 )
	##     } else {
	##         res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=org.Hs.eg.db, upstream=2000, downstream=1000 )
	##     }
	## 
	## }
	## if ( REFGENOME == 'dm6' ) {
	##     if ( typeOfpeakCalling == 'MACSnarrow' ) {
	##         GRtmp <- GR
	##         start(GRtmp) <- GR$summit
	##         end(GRtmp) <- GR$summit
	##         res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=org.Dm.eg.db, upstream=2000, downstream=1000 )
	##     } else {
	##         res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=org.Dm.eg.db, upstream=2000, downstream=1000 )
	##     }
	## }
	## if ( REFGENOME == 'rn5' ) {
	##     if ( typeOfpeakCalling == 'MACSnarrow' ) {
	##         GRtmp <- GR
	##         start(GRtmp) <- GR$summit
	##         end(GRtmp) <- GR$summit
	##         res <- GRannotate( Object=GRtmp, txdb=txdb, EG2GS=org.Rn.eg.db, upstream=2000, downstream=1000 )
	##     } else {
	##         res <- GRannotate( Object=GRmidpoint(GR), txdb=txdb, EG2GS=org.Rn.eg.db, upstream=2000, downstream=1000 )
	##     }
	## }
	ranges(res) <- ranges(GR)
	return (res)
}

makeBigBedFile <- function( fileBED, genomePaths, GenomeBrowserFolder ){
	fileBEDtmp <- paste0( substr( fileBED, start=1, stop=nchar( fileBED )-3 ), 'tmp' )
	fileBEDtmp2 <- paste0( substr( fileBED, start=1, stop=nchar( fileBED )-3 ), 'tmp2' )
	fileBB <- paste0( substr( fileBED, start=1, stop=nchar( fileBED )-3 ), 'bb' )
	loginfo('Subsetting BED file to first 4 columns')
	execute <- paste0(
			"awk 'match($0,\"chr\"){ print( $1 \"\t\" $2 \"\t\" $3 \"\t\" $4 ) }' "
			,fileBED
			," > "
			,fileBEDtmp
	)
	
	tryOrExit(execute,  "subset BED file")
	
	execute <- paste0(
			getHTSFlowPath("bedClip")
			, ' '
			,fileBEDtmp
			,' '
			,chromSize <- genomePaths["chromSize",]
			,' '
			,fileBEDtmp2
	)
	tryOrExit(execute,  "bedClip")
	
	execute <- paste0(
			getHTSFlowPath("bedToBigBed")
			," "
			,fileBEDtmp2
			," "
			,chromSize <- genomePaths["chromSize",]
			," "
			,fileBB
	)
	tryOrExit(execute,  "bedToBigBed")
	
	deleteFile(fileBEDtmp)
	deleteFile(fileBEDtmp2)
	
	
	cmd <- paste0( 'mv ', fileBB, ' ', GenomeBrowserFolder )
	tryOrExit(cmd,  "move files")
}



peakcaller <- function( IDpeak, INPUT_ID, CHIP_ID, label, pvalue, stats, IDsec_FOLDER, BAMfolder, macsOUT, REFGENOME, saturation, typeOfpeakCalling ) {
	
	basicConfig(level="DEBUG")
	
	INPUT_BAM <- paste0( BAMfolder, INPUT_ID, ".bam" )
	CHIP_BAM <- paste0( BAMfolder, CHIP_ID, ".bam" )
	
	
	if ( typeOfpeakCalling == 'MACSnarrow' ) {
		narrow<- 'NARROW'
	} else {
		narrow<- 'BROAD'
	}
	
	
	if ( saturation ) {		
		
		CHIP_BAM_80 <- paste0( IDsec_FOLDER, IDpeak, "_", CHIP_ID, "_80.bam" )
		CHIP_BAM_60 <- paste0( IDsec_FOLDER, IDpeak, "_", CHIP_ID, "_60.bam" )
		CHIP_BAM_40 <- paste0( IDsec_FOLDER, IDpeak, "_", CHIP_ID, "_40.bam" )
		CHIP_BAM_20 <- paste0( IDsec_FOLDER, IDpeak, "_", CHIP_ID, "_20.bam" )
		
		# check if bam are missing
		
		batch_CHIP_BAM <- c(CHIP_BAM_80,CHIP_BAM_60,CHIP_BAM_40,CHIP_BAM_20)
		batch_label<- c(paste0( IDpeak, "_", label, "_80" ), paste0( IDpeak, "_", label, "_60" ), paste0( IDpeak, "_", label, "_40" ), paste0( IDpeak, "_", label, "_20" ))
		
		# load config and common functions
		workdir <- getwd()
		setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))

		loadConfig("BatchJobs.R")
		setwd(workdir)
		
## # Ensure we use tha maximum of jobs alowed.
##         config <-getConfig()
##         config["max.concurrent.jobs"] <- "Inf"
##         setConfig(config)
## 
##         loginfo(getConfig())
## 
		
		regName <- paste0("HF_MACS2_",IDpeak,"_",narrow)
		reg <- makeHtsflowRegistry(regName)
		
		ids <- batchMap(reg, fun=macs2Exec, rep(INPUT_BAM, 4), batch_CHIP_BAM, batch_label, rep(pvalue, 4),
				rep(stats, 4), rep(macsOUT, 4), rep(REFGENOME, 4), rep(narrow, 4))
		
		submitJobs(reg)
				
		showStatus(reg)
		
		loginfo("MACS2 for subsets launched in parallel, continue with the main ChIP")
		
		# run the main peak calling, then wait for the other ones.
		macs2Exec( INPUT_BAM, CHIP_BAM, label, pvalue, stats, macsOUT, REFGENOME, narrow )
		
		waitForJobs(reg)
						
		errors<-findErrors(reg)
		
		if (length(errors) > 0) {
			## removeRegistry(reg,"no")
			stop(paste0(length(errors), " job(s) failed, exit"))
		}
	
		create_saturation_file( label, macsOUT )
	} else {	
		macs2Exec( INPUT_BAM, CHIP_BAM, label, pvalue, stats, macsOUT, REFGENOME, narrow )
	}
	
}
