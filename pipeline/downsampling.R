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
#source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/commons/config.R"))


downsampling <- function ( flags, flagsPRE, id_down_primary ) {
	
	# HTS-flow2 : id_merge is the sample id	
	
	loginfo ("Start downsampling")

	# update the DB with the secondary analysis status deconvolute expression
	SQL <-
			paste(
					"UPDATE primary_analysis SET status='downsampling bam..', dateStart=NOW() WHERE id="
					,id_down_primary
					,sep=""
			)
	res <- updateInfoOnDB( SQL )
		
	SQL <- paste0(
			"SELECT source_primary_id as id_pre_fk, reads_num, seq_method, target_reads_number FROM downsample_primary, primary_analysis, sample WHERE result_primary_id = "
			,id_down_primary
			," AND source_primary_id = primary_analysis.id AND sample.id=sample_id;"
	)
	results <- extractInfoFromDB( SQL )
	print(SQL)
	BAMfolder <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"),"/" )	
	BAMin <- paste0( BAMfolder, results$id, ".bam")
	
	outFolder <- getPreprocessDir()
	BAMout <- paste0( outFolder, id_down_primary, ".bam") 
	
	
	seq_method <- results$seq_method
	originalNumberOfReads <- as.integer(results$reads_num)		
	targetNumberOfReads <- as.integer(results$target_reads_number)	
	
	loginfo (paste0("Downsampling: number of reads= ", originalNumberOfReads, " -> ", targetNumberOfReads))
	
	
	percentage = targetNumberOfReads / originalNumberOfReads
	loginfo (paste0("Downsampling: fraction of reads to extract = ", percentage))
	

	removeReadsFromBAM( BAMin, BAMout, percentage ) 
	
	# create .bai file
	execute <- paste(
			getHTSFlowPath("samtools")
			," index "
			,BAMout
			,sep = ""
	)
	tryOrExit(execute, "samtool, index")
	
	# # count reads in new bam
	
	execute <- paste(
			getHTSFlowPath("samtools")
			, " view -c  "
			, BAMout
			, sep = ""
	)
	
	nReads <- tryInternOrExit( execute, "downsampling, samtool view")
	
	loginfo ( paste ( BAMout, ", number of reads: ", nReads, sep="" ) )
	
	# update the DB with the filtering status and the file location
	SQL <- paste0(
			"UPDATE primary_analysis SET reads_num="
			,nReads
			," WHERE id="
			,id_down_primary
	)
	res <- updateInfoOnDB( SQL )
	
	## seq_method <- flags$seq_method
		
	# if method = RNAseq we have to perform the reads count.
	
	reads_modeSQL <- paste0("SELECT reads_mode FROM sample, primary_analysis WHERE sample.id=sample_id AND primary_analysis.id = ", id_down_primary ,";")	
	ref_genomeSQL <- paste0("SELECT ref_genome FROM sample, primary_analysis WHERE sample.id=sample_id AND primary_analysis.id = ", id_down_primary ,";")
	
	reads_mode <- extractSingleColumnFromDB( reads_modeSQL )
	ref_genome <- extractSingleColumnFromDB( ref_genomeSQL )
	
	if (dim(ref_genome)[1] > 1) {
		RefGenomes <- getGenomePaths( ref_genome[1,] )
	} else {
		RefGenomes <- getGenomePaths( ref_genome )
	}
	
	if ( dim( reads_mode )[1] > 1 ) {
		paired = 0
	} else {
		if (reads_mode == "SR") {
			paired = 0
		} else {
			paired = 1
		}
	}
	
	
	
	COUNTdir <- paste0( getHTSFlowPath("HTSFLOW_COUNT"), "/" )
	countfile <- paste( COUNTdir, id_down_primary, ".count" , sep='' )
	
	gtf <- RefGenomes["genes",]
	
	print(seq_method)
	
	if (seq_method == "rna-seq"){
		loginfo ("Counting reads per gene with featuresCounts ---")
		
		# update the DB with the secondary analysis status counting reads
		SQL <-
				paste(
						"UPDATE primary_analysis SET status='deconvolute reads..'"
						," WHERE id="
						,id_down_primary
						,sep=""
				)
		updateInfoOnDB( SQL )
		
		if ( paired ) {
			execute <- paste(
					getHTSFlowPath("featuresCounts")
					,"-T 16 -p -P -a"
					,gtf
					,"-o"
					,countfile
					,BAMout
					,sep=" "
			)
			tryOrExit(execute, "feature counts")
		} else {
			execute <- paste(
					getHTSFlowPath("featuresCounts")
					,"-T 16 -a"
					,gtf
					,"-o"
					,countfile
					,BAMout
					,sep=" "
			)
			tryOrExit(execute, "feature counts")
		}
	}
	
	
	execute <- paste0(
			"mv "
			,BAMout
			," "
			,BAMfolder
			,id_down_primary
			,".bam"
	)
	tryOrExit(execute, "move bam")
	
	execute <- paste0(
			"mv "
			,BAMout
			,".bai "
			,BAMfolder
			,id_down_primary
			,".bam.bai"
	)
	tryOrExit(execute, "move .bai")
	
	# Then we have to create the bw files and copy on track_manager folder.

	createBigWig(id_down_primary, RefGenomes, seq_method, as.numeric( flagsPRE$stranded))

	
	# update the DB with the secondary analysis status complete
	SQL <-
			paste(
					"UPDATE primary_analysis SET status='completed', dateEnd=NOW() "
					,"WHERE id="
					,id_down_primary
					,sep=""
			)
	updateInfoOnDB( SQL )
	#########################################
	
}

