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

# Functions to generate bigwig

createBigWig <- function(primaryId, genomePaths, seq_method, stranded) {

	createSingleBigWig( primaryId, genomePaths, seq_method )
		
	if ( seq_method=="rna-seq" && as.numeric( stranded) ) {			
		createSingleBigWig( primaryId, genomePaths, seq_method, "+" )
		createSingleBigWig( primaryId, genomePaths, seq_method, "-" )
	
		# Remove stranded bams		
		bam_p <- paste0(getHTSFlowPath("HTSFLOW_ALN"),'/',primaryId,'_p.bam')
		bam_n <- paste0(getHTSFlowPath("HTSFLOW_ALN"),'/',primaryId,'_n.bam')
	
		deleteFile(bam_p)
		deleteFile(bam_n)			
	}	
	
}


# In case of strand specific alignment, specify
# strand = + or strand = -. The bam files used
# will be the one ended with _p or _n respectively
# If strand is null or empty,the main bam file is used.
createSingleBigWig <- function( primaryId, genomePaths, seq_method, strand = FALSE){
	
	if ( seq_method == 'bs-seq' ) {
		chromSize <- genomePaths[ "chromSize_bs", ]
	} else {
		chromSize <- genomePaths[ "chromSize", ]
	}
	
	# this is a patch for having all the bw in the genome browser
	BWOUTFOLDER <-  getHTSFlowPath("HTSFLOW_BW")
	
	if (! file.exists(BWOUTFOLDER)){
		loginfo(paste("create directory: ", BWOUTFOLDER))
		createDir(BWOUTFOLDER,  recursive =  TRUE)		
	}
	
	SQL <- paste0("SELECT reads_num FROM primary_analysis WHERE id=",primaryId,";")
	readsAln <- extractInfoFromDB( SQL )
	
	
	# Bam id is equal to primary id for unstranded alignment. For stranded alignment a suffix is added.
	if (strand == "+") {
		loginfo ( "Generating BW file: strand +")
		bamId <- paste0(primaryId, "_p")
	} else if (strand == "-") {
		loginfo ( "Generating BW file: strand +")
		bamId <- paste0(primaryId, "_n")
	} else {
		loginfo ( "Generating BW file: unstranded")
		bamId <- primaryId
		
	}

	
	# The BAM file for stranded alignment is not stored, we should create it temporaly
	if (strand ==  "+" || strand == "-") {		
		bam <- paste0(getPreprocessDir(),'/',bamId,'.bam') #,'/',primaryId,'.bam')
		sam <- paste0(getPreprocessDir(),bamId,'.sam')
		
		bamFileName <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"), "/", primaryId, ".bam" )
		str=paste0(getHTSFlowPath("samtools"), ' view ', bamFileName, ' | grep -v ERCC | grep XS:A:', strand, ' > ', sam)		
		tryOrExit(str, "Extract strand")
		
		str=paste(getHTSFlowPath("samtools"), '  view -bT', genomePaths["fasta",] , sam, '>', bam)
		tryOrExit(str, "Write BAM for positive strand")
	} else {
		bam <- paste0 (
			getHTSFlowPath("HTSFLOW_ALN")
			,"/"
			,bamId
			,".bam"
		)
	}
		
	bedgraphF <- paste0(
			getPreprocessDir()
			,bamId
			,".bedgraph"
	)
	
	bedgraphTmp <- paste0(
			getPreprocessDir()
			,bamId
			,".tmp"
	)
	
	bwF <- paste0(
			getPreprocessDir()
			,bamId
			,".bw"
	)
	
	execute <- paste0(
			getHTSFlowPath("genomeCoverageBed")
			," -split -ibam "
			,bam
			," -g "
			,chromSize
			," -bg > "
			,bedgraphF
	)
	
	tryOrExit(execute, "BW-gcb")
	
	execute <- paste0(
			"awk '{print $1,$2,$3,int($4*1e7/"
			,readsAln
			,")+1}' "
			,bedgraphF
			," >  "
			,bedgraphTmp
	)
	
	tryOrExit(execute, "BW-awk")
	
	execute <- paste0(
			getHTSFlowPath("bedGraphToBigWig")
			," "
			,bedgraphTmp
			," "
			,chromSize
			," "
			,bwF
	)
	
	tryOrExit(execute, "BW-bgw")
	
	deleteFile(bedgraphF, recursive=TRUE)
	deleteFile(bedgraphTmp, recursive=TRUE)
	
	loginfo ('Copying BW file to Genome Browser folder')
	execute <- paste0( 'mv ',bwF, ' ',BWOUTFOLDER )
	result <- tryOrExit(execute, "BW")
	
	loginfo ( "BW done.")
	return (result)
}
