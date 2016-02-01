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
source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/commons/config.R"))

merging <- function ( flags, flagsPRE, id_merge_primary ) {
	# HTS-flow2 : id_merge is the sample id	
	
	loginfo ("Start merging")

	# update the DB with the secondary analysis status deconvolute expression
	SQL <-
			paste(
					"UPDATE primary_analysis SET status='merging bam..', dateStart=NOW() WHERE id="
					,id_merge_primary
					,sep=""
			)
	res <- updateInfoOnDB( SQL )
	
	# 1 - create the folder in the output named with the ID of merge table
	# 2 - samtools merge out.bam in1.bam in2.bam in3.bam ..
	SQL <- paste0(
			"SELECT source_primary_id as id_pre_fk FROM merged_primary WHERE result_primary_id = "
			,id_merge_primary
			,";"
	)
	results <- extractSingleColumnFromDB( SQL )
	BAMfolder <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"),"/" )
	outFolder <- getPreprocessDir()
	outFile <- paste0 ( outFolder, id_merge_primary, ".bam" )
	BAMin <- as.vector( sapply(results, function(x) paste0( BAMfolder, x, ".bam") ) )
	
	# merge command
	execute <- paste ( getHTSFlowPath("samtools"), "merge", outFile)
	for (i in 1:length(BAMin)) {
		execute <- paste(execute, BAMin[i])
	}
	
	# create merged file
	tryOrExit(execute, "samtool, merge")
	
	# sort the bam samtools sort aln.bam aln.sorted
	outFileSortTmp <- paste0( outFolder, id_merge_primary, "_sort" )
	outFileSort <- paste0( outFolder, id_merge_primary, "_sort.bam")
	
	# update the DB with the secondary analysis status deconvolute expression
	SQL <- paste("UPDATE primary_analysis SET status='sorting bam..' WHERE id=",id_merge_primary,sep="")
	res <- updateInfoOnDB( SQL )
	
	execute <- paste(
			getHTSFlowPath("samtools")
			," sort "
			,outFile
			," "
			,outFileSortTmp
			,sep = ""
	)
	tryOrExit(execute, "samtool, sort")
	
	if ( as.logical( as.numeric( flagsPRE$rm_duplicates ) ) ) {
		
		# update the DB with the secondary analysis status deconvolute expression
		SQL <-
				paste(
						"UPDATE primary_analysis SET status='removing duplicates..' WHERE id="
						,id_merge_primary
						,sep=""
				)
		res <- updateInfoOnDB( SQL )
		
		execute <- paste(
				getHTSFlowPath("samtools")
				,' rmdup -sS '
				,outFileSort
				," "
				,outFile
				,sep = ""
		)
		tryOrExit(execute, "samtool, rmdup")
		
	}
	
	# update the DB with the secondary analysis status deconvolute expression
	SQL <-
			paste(
					"UPDATE primary_analysis SET status='indexing bam..' WHERE id="
					,id_merge_primary
					,sep=""
			)
	res <- updateInfoOnDB( SQL )
	
	# create .bai file
	execute <- paste(
			getHTSFlowPath("samtools")
			," index "
			,outFile
			,sep = ""
	)
	tryOrExit(execute, "samtool, index")
	
	# # count reads in new bam
	
	execute <- paste(
			getHTSFlowPath("samtools")
			, " view -c  "
			, outFile
			, sep = ""
	)
	
	nReads <- tryInternOrExit( execute, "merge, samtool view")
	
	loginfo ( paste ( outFile, ", number of reads: ", nReads, sep="" ) )
	
	# update the DB with the filtering status and the file location
	SQL <- paste0(
			"UPDATE primary_analysis SET reads_num="
			,nReads
			," WHERE id="
			,id_merge_primary
	)
	res <- updateInfoOnDB( SQL )
	
	# remove the tmp file
	execute <- paste0( "rm ", outFileSort )
	tryOrExit(execute, "remove files")
	
	seq_method <- flags$seq_method
		
	# if method = RNAseq we have to perform the reads count.
	
	reads_modeSQL <- paste0("SELECT reads_mode FROM sample, primary_analysis WHERE sample.id=sample_id AND primary_analysis.id = ", id_merge_primary ,";")	
	ref_genomeSQL <- paste0("SELECT ref_genome FROM sample, primary_analysis WHERE sample.id=sample_id AND primary_analysis.id = ", id_merge_primary ,";")
	
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
	ALNdir <- paste0( getHTSFlowPath("HTSFLOW_ALN"), "/" )
	bamfile <- paste( outFolder, id_merge_primary, ".bam", sep="" )
	countfile <- paste( COUNTdir, id_merge_primary, ".count" , sep='' )
	gtf <- RefGenomes["genes",]
	
	if (seq_method == "RNA-Seq"){
		loginfo ("Counting reads per gene with featuresCounts ---")
		
		# update the DB with the secondary analysis status counting reads
		SQL <-
				paste(
						"UPDATE primary_analysis SET status='deconvolute reads..'"
						," WHERE id="
						,id_merge_primary
						,sep=""
				)
		updateInfoOnDB( SQL )
		
		if ( paired ) {
			cmd <- paste(
					getHTSFlowPath("featuresCounts")
					,"-T 16 -p -P -a"
					,gtf
					,"-o"
					,countfile
					,bamfile
					,sep=" "
			)
			tryOrExit(execute, "feature counts")
		} else {
			cmd <- paste(
					getHTSFlowPath("featuresCounts")
					,"-T 16 -a"
					,gtf
					,"-o"
					,countfile
					,bamfile
					,sep=" "
			)
			tryOrExit(execute, "feature counts")
		}
	}
	
	
	execute <- paste0(
			"mv "
			,bamfile
			," "
			,getHTSFlowPath("HTSFLOW_ALN")
			,"/"
			,id_merge_primary
			,".bam"
	)
	tryOrExit(execute, "move bam")
	
	execute <- paste0(
			"mv "
			,bamfile
			,".bai "
			,ALNdirq
			,id_merge_primary
			,".bam.bai"
	)
	tryOrExit(execute, "move .bai")
	
	# Then we have to create the bw files and copy on track_manager folder.
	
	makeBWmerge( id_merge_primary, RefGenomes )

	
	# update the DB with the secondary analysis status complete
	SQL <-
			paste(
					"UPDATE primary_analysis SET status='completed', dateEnd=NOW() "
					,"WHERE id="
					,id_merge_primary
					,sep=""
			)
	updateInfoOnDB( SQL )
	#########################################
	
}

makeBWmerge <- function( sample, RefGenomes ){
	loginfo ( "Generating BW file")
	chromSize <- RefGenomes[ "chromSize", ]
	primaryId <- sample
	# this is a patch for having all the bw in the genome browser
	BWOUTFOLDER <- paste0(getHTSFlowPath("HTSFLOW_PRIMARY"), '/tracks/bw/')
	bamFile <- paste0 (
			getHTSFlowPath("HTSFLOW_ALN")
			,"/"
			,primaryId
			,".bam"
	)
	SQL <- paste0("SELECT reads_num FROM primary_analysis WHERE id=",primaryId,";")
	readsAln <- extractInfoFromDB( SQL )
	
	bedgraphF <- paste0(
			getPreprocessDir()
			,primaryId
			,".bedgraph"
	)
	
	bedgraphTmp <- paste0(
			getPreprocessDir()
			,primaryId
			,".tmp"
	)
	
	bwF <- paste0(
			getPreprocessDir()
			,primaryId
			,".bw"
	)
	
	execute <- paste0(
			getHTSFlowPath("genomeCoverageBed")
			," -split -ibam "
			,bamFile
			," -g "
			,chromSize
			," -bg > "
			,bedgraphF
	)
	tryOrExit(execute, "make BW merge: genome coverage BED")
	
	execute <- paste0(
			"awk '{print $1,$2,$3,int($4*1e7/"
			,readsAln
			,")+1}' "
			,bedgraphF
			," >  "
			,bedgraphTmp
	)
	tryOrExit(execute, "make BW merge: awk")
	
	execute <- paste0(
			getHTSFlowPath("bedGraphToBigWig")
			," "
			,bedgraphTmp
			," "
			,chromSize
			," "
			,bwF
	)
	tryOrExit(execute, "make BW merge: BED graph to BigWig")
	
	execute <- paste0(
			"rm -f "
			,bedgraphF
			," "
			,bedgraphTmp
	)
	tryOrExit(execute, "make BW merge: clean")
	
	print ('Copying BW file to Genome Browser folder')
	execute <- paste0( 'mv ',bwF, ' ',BWOUTFOLDER )
	result <- tryOrExit(execute, "make BW merge: move files")
	
	loginfo ( "BW done.")
	return(result)
}


