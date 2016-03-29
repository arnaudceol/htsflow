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

inspect <- function( IDsec ){
	method = "inspect"
	
	initSpecies()
	
	loginfo ("Start INSPEcT")
	## exp_name <- "x"
	values <- getSecondaryData(IDsec, method)
	
	sqlType <- paste( "SELECT type FROM inspect WHERE secondary_id=",IDsec," LIMIT 1;")
	tmp <- dbQuery(sqlType)
	inspectType <- tmp[2]
	
	
	
	sqlGenome <- paste( "SELECT ref_genome FROM inspect, primary_analysis, sample WHERE primary_id = primary_analysis.id AND sample.id = sample_id AND secondary_id=",IDsec," LIMIT 1;")
	tmp <- dbQuery(sqlGenome)
	genome <- tmp[2]
	
	txdbLib <- htsflowGenomeVersionsToTxdbLib[[genome]]
	
	library(INSPEcT)
	library(txdbLib, character.only=TRUE)
	txdb <-get(txdbLib)
	
	
	labeling_time <- as.numeric(values$labeling_time[1])
	degDuringPulse <- sapply(values$deg_during_pulse, function (x) if (x[1] == "0") { FALSE ;} else{ TRUE;})[1]
	modeling_rates <- sapply(values$modeling_rates, function (x) if (x[1] == "0") { FALSE ;} else{ TRUE;})[1]
	counts_filtering <- as.numeric(values$counts_filtering[1])
	
	bamFolder <- getHTSFlowPath("HTSFLOW_ALN")
	
	
	if (inspectType == "time_course") {
		
		
		# txdb --- TranscriptDB object
# timepoints ---- numeric vector
# bamfiles_4sU ---- character vector -> file path
# bamfiles_RNAtotal ---- --> character vector
# labeling_time ---- numeric --> 
# degDuringPulse --- logical, default=FALSE
# modeling_rates --- logical, default=FALSE
# counts_filtering --- numeric, default=5
		
		
		fourSuBams <- sapply(1:length(values$primary_id), function(x) paste(bamFolder, values$primary_id[x], ".bam", sep="") )
		rnaTotalBams <- sapply(1:length(values$rnatotal_id), function(x) paste(bamFolder, values$primary_id[x], ".bam", sep="") )
		timepoints <-  as.numeric(values$timepoint)
		
		## NOTE: timepoints, bamfiles_4sU and bamfiles_RNAtotal must have the same length
		
		## quantification of intron and exon features for each bamfile
		
		quantifyExInt <- makeRPKMs(txdb, fourSuBams, rnaTotalBams)
		rpkms <- quantifyExInt$rpkms
		counts <- quantifyExInt$counts
		
		# filtering based on counts?
		
		exonFeatures <- rownames(rpkms$foursu_exons)
		intronFeatures <- rownames(rpkms$foursu_introns)
		exon_filt <- exonFeatures[apply(counts$foursu$exonCounts>=counts_filtering,1,all) &
						apply(counts$total$exonCounts>=counts_filtering,1,all)]
		intron_filt <- intronFeatures[apply(counts$foursu$intronCounts>=counts_filtering,1,all) &
						apply(counts$total$intronCounts>=counts_filtering,1,all)]
		intron_filt <- intersect(intron_filt, exon_filt)
		
		rpkms_foursu_exons <- rpkms$foursu_exons[exon_filt,,drop=FALSE]
		rpkms_total_exons <- rpkms$total_exons[exon_filt,,drop=FALSE]
		rpkms_foursu_introns <- rpkms$foursu_introns[intron_filt,,drop=FALSE]
		rpkms_total_introns <- rpkms$total_introns[intron_filt,,drop=FALSE]
		
		counts_foursu_exons <- counts$foursu$exonCounts[exon_filt,,drop=FALSE]
		counts_total_exons <- counts$total$exonCounts[exon_filt,,drop=FALSE]
		counts_foursu_introns <- counts$foursu$intronCounts[intron_filt,,drop=FALSE]
		counts_total_introns <- counts$total$intronCounts[intron_filt,,drop=FALSE]
		
		
		## quantification of rates
		
		inspectIds <- newINSPEcT(
				timepoints, labeling_time, 
				rpkms_foursu_exons, rpkms_total_exons, 
				rpkms_foursu_introns, rpkms_total_introns, 
				degDuringPulse=degDuringPulse, BPPARAM=SerialParam()
		)
		
		## modeling of rates
		
		if( modeling_rates ) {
			inspectIds_mod <- modelRates(inspectIds, seed=1)
		}
		
		#### inspect specific output 
		#### matrices with row=genes and columns=unique(timepoints)
		
		sythesis <- ratesFirstGuess(inspectIds, 'synthesis')
		degradation <- ratesFirstGuess(inspectIds, 'degradation')
		processing <- ratesFirstGuess(inspectIds, 'processing')
		pre_mRNA <- ratesFirstGuess(inspectIds, 'preMRNA')
		total_mRNA <- ratesFirstGuess(inspectIds, 'total')
		
		if( modeling_rates ) {
			
			modeled_sythesis <- viewModelRates(inspectIds, 'synthesis')
			modeled_degradation <- viewModelRates(inspectIds, 'degradation')
			modeled_processing <- viewModelRates(inspectIds, 'processing')
			modeled_pre_mRNA <- viewModelRates(inspectIds, 'preMRNA')
			modeled_total_mRNA <- viewModelRates(inspectIds, 'total')
			
		}
		
		#### other output 
		#### matrices with row=genes and columns=timepoints
# rpkms_foursu_exons
# rpkms_total_exons
# rpkms_foursu_introns
# rpkms_total_introns
# counts_foursu_exons
# counts_total_exons
# counts_foursu_introns
# counts_total_introns
		
		
		
		# creating output folder
		outFolder <- paste ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/", sep="" )
		loginfo( paste("Create output folder ",outFolder," --",sep="") )
		createDir(outFolder,  recursive =  TRUE)	
		setwd( outFolder )
		
		saveRDS( sythesis, paste0( "synthesis.rds" ) )
		saveRDS( degradation, paste0( "degradation.rds" ) )
		saveRDS( processing, paste0( "processing.rds" ) )
		saveRDS( pre_mRNA, paste0( "pre_mRNA.rds" ) )
		saveRDS( total_mRNA, paste0( "total_mRNA..rds" ) )
		
		# update the DB with the secondary analysis status complete
		setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
		
		
	} else  {
		
		
		# fourSuBams <- sapply(1:length(values$primary_id), function(x) paste(bamFolder, values$primary_id[x], ".bam", sep="") )
		# rnaTotalBams <- sapply(1:length(values$rnatotal_id), function(x) paste(bamFolder, values$primary_id[x], ".bam", sep="") )
		# timepoints <-  as.numeric(values$timepoint)
		
		# Get conditions:
		condition1 <- unique(values$cond)[1]
		condition2 <- unique(values$cond)[2]
		
		# For each row, add in the good condition one	
		
		bamfiles_4sU_cond1 <- c()
		bamfiles_RNAtotal_cond1 <- c()
		
		bamfiles_4sU_cond2 <- c()
		bamfiles_RNAtotal_cond2 <- c()
		
		for (x in 1:length(values$primary_id)) {
		## sapply(1:length(values$primary_id), function(x)
				if (values$cond[x] == condition1) {
						bamfiles_4sU_cond1 <- c(bamfiles_4sU_cond1, paste(bamFolder, values$primary_id[x], ".bam", sep="")); 
						bamfiles_RNAtotal_cond1 <- c(bamfiles_RNAtotal_cond1, paste(bamFolder, values$rnatotal_id[x], ".bam", sep="")); 
					} else  {
						bamfiles_4sU_cond2 <- c(bamfiles_4sU_cond2, paste(bamFolder, values$primary_id[x], ".bam", sep="")); 
						bamfiles_RNAtotal_cond2 <- c(bamfiles_RNAtotal_cond2, paste(bamFolder, values$rnatotal_id[x], ".bam", sep="")); 
					}
		}
		
		quantifyExInt_cond1 <- makeRPKMs(txdb, bamfiles_4sU_cond1, bamfiles_RNAtotal_cond1)
		quantifyExInt_cond2 <- makeRPKMs(txdb, bamfiles_4sU_cond2, bamfiles_RNAtotal_cond2)
		
		rpkms_cond1 <- quantifyExInt_cond1$rpkms
		counts_cond1 <- quantifyExInt_cond1$counts
		
		rpkms_cond2 <- quantifyExInt_cond2$rpkms
		counts_cond2 <- quantifyExInt_cond2$counts
		

		
		# cond 1

		## filtering based on counts

		exonFeatures <- rownames(rpkms_cond1$foursu_exons)
		intronFeatures <- rownames(rpkms_cond1$foursu_introns)
		exon_filt <- exonFeatures[apply(counts_cond1$foursu$exonCounts>=counts_filtering,1,all) &
				apply(counts_cond1$total$exonCounts>=counts_filtering,1,all)]
		intron_filt <- intronFeatures[apply(counts_cond1$foursu$intronCounts>=counts_filtering,1,all) &
				apply(counts_cond1$total$intronCounts>=counts_filtering,1,all)]
		intron_filt <- intersect(intron_filt, exon_filt)

		rpkms_cond1_foursu_exons <- rpkms_cond1$foursu_exons[exon_filt,,drop=FALSE]
		rpkms_cond1_total_exons <- rpkms_cond1$total_exons[exon_filt,,drop=FALSE]
		rpkms_cond1_foursu_introns <- rpkms_cond1$foursu_introns[intron_filt,,drop=FALSE]
		rpkms_cond1_total_introns <- rpkms_cond1$total_introns[intron_filt,,drop=FALSE]
		
		counts_cond1_foursu_exons <- counts_cond1$foursu$exonCounts[exon_filt,,drop=FALSE]
		counts_cond1_total_exons <- counts_cond1$total$exonCounts[exon_filt,,drop=FALSE]
		counts_cond1_foursu_introns <- counts_cond1$foursu$intronCounts[intron_filt,,drop=FALSE]
		counts_cond1_total_introns <- counts_cond1$total$intronCounts[intron_filt,,drop=FALSE]
			
		# cond 2
		
		## filtering based on counts

		exonFeatures <- rownames(rpkms_cond2$foursu_exons)
		intronFeatures <- rownames(rpkms_cond2$foursu_introns)
		exon_filt <- exonFeatures[apply(counts_cond2$foursu$exonCounts>=counts_filtering,1,all) &
				apply(counts_cond2$total$exonCounts>=counts_filtering,1,all)]
		intron_filt <- intronFeatures[apply(counts_cond2$foursu$intronCounts>=counts_filtering,1,all) &
				apply(counts_cond2$total$intronCounts>=counts_filtering,1,all)]
		intron_filt <- intersect(intron_filt, exon_filt)


		rpkms_cond2_foursu_exons <- rpkms_cond2$foursu_exons[exon_filt,,drop=FALSE]
		rpkms_cond2_total_exons <- rpkms_cond2$total_exons[exon_filt,,drop=FALSE]
		rpkms_cond2_foursu_introns <- rpkms_cond2$foursu_introns[intron_filt,,drop=FALSE]
		rpkms_cond2_total_introns <- rpkms_cond2$total_introns[intron_filt,,drop=FALSE]
		
		counts_cond2_foursu_exons <- counts_cond2$foursu$exonCounts[exon_filt,,drop=FALSE]
		counts_cond2_total_exons <- counts_cond2$total$exonCounts[exon_filt,,drop=FALSE]
		counts_cond2_foursu_introns <- counts_cond2$foursu$intronCounts[intron_filt,,drop=FALSE]
		counts_cond2_total_introns <- counts_cond2$total$intronCounts[intron_filt,,drop=FALSE]
		
		
		## todo
		
		## quantification of rates
		
		timepoints <- rep(0, length(bamfiles_4sU_cond1))
		inspectIds1 <- newINSPEcT(
				timepoints, labeling_time, 
				rpkms_cond1_foursu_exons, rpkms_cond1_total_exons, 
				rpkms_cond1_foursu_introns, rpkms_cond1_total_introns, 
				degDuringPulse=degDuringPulse,BPPARAM=SerialParam()
		)
		
		timepoints <- rep(0, length(bamfiles_4sU_cond2))
		inspectIds2 <- newINSPEcT(
				timepoints, labeling_time, 
				rpkms_cond2_foursu_exons, rpkms_cond2_total_exons, 
				rpkms_cond2_foursu_introns, rpkms_cond2_total_introns, 
				degDuringPulse=degDuringPulse,BPPARAM=SerialParam()
		)
		
		## differential analysis
		
		diffrates <- compareSteady(inspectIds1, inspectIds2)
		
		#### inspect specific output 
		#### matrices with row=genes and columns=10
		
		diff_synthesis <- synthesis(diffrates)
		diff_processing <- processing(diffrates)
		diff_degradation <- degradation(diffrates)
		
		#### other output 
		#### matrices with row=genes and columns=4
		
		all_rpkms_cond1 <- list(
				foursu_exons = rpkms_cond1_foursu_exons[,1]
				,total_exons = rpkms_cond1_total_exons[,1]
				,foursu_introns = rpkms_cond1_foursu_introns[,1]
				,total_introns = rpkms_cond1_total_introns[,1]
		)
		
		all_counts_cond1 <- list(
				foursu_exons = counts_cond1_foursu_exons[,1]
				,total_exons = counts_cond1_total_exons[,1]
				,foursu_introns = counts_cond1_foursu_introns[,1]
				,total_introns = counts_cond1_total_introns[,1]
		)
		
		all_rpkms_cond2 <- list(
				foursu_exons = rpkms_cond2_foursu_exons[,1]
				,total_exons = rpkms_cond2_total_exons[,1]
				,foursu_introns = rpkms_cond2_foursu_introns[,1]
				,total_introns = rpkms_cond2_total_introns[,1]
		)
		
		all_counts_cond2 <- list(
				foursu_exons = counts_cond2_foursu_exons[,1]
				,total_exons = counts_cond2_total_exons[,1]
				,foursu_introns = counts_cond2_foursu_introns[,1]
				,total_introns = counts_cond2_total_introns[,1]
		)
		
		
		# creating output folder
		outFolder <- paste ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/", sep="" )
		loginfo( paste("Create output folder ",outFolder," --",sep="") )
		createDir(outFolder,  recursive =  TRUE)	
		setwd( outFolder )
		
		
		
		saveRDS( diffrates, paste0( "diffrates.rds" ) )
		saveRDS( diff_synthesis, paste0( "diff_synthesis.rds" ) )
		saveRDS( diff_processing, paste0( "diff_processing.rds" ) )
		saveRDS( diff_degradation, paste0( "diff_degradation.rds" ) )
		
		saveRDS( all_rpkms_cond1, paste0( "all_rpkms_cond1.rds" ) )
		saveRDS( all_rpkms_cond2, paste0( "all_rpkms_cond2.rds" ) )
		saveRDS( all_counts_cond1, paste0( "all_counts_cond1.rds" ) )
		saveRDS( all_counts_cond2, paste0( "pre_all_counts_cond2.rds" ) )
		
		# update the DB with the secondary analysis status complete
		setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
		
		
		
		
	}
	
	
}
