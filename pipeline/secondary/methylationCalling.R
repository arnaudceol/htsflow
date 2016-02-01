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

methylationCalling <- function( IDsec ) {
	method = "methylation_calling"

	loginfo ("Start DNA Methylation Calling")

	values <- getSecondaryData(IDsec, method)	
	
	
	library(methylPipe, quietly = TRUE)
	SAMfolder <- getPreprocessDir()
	
	outFolder <- paste0 ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/" )
	createDir(outFolder,  recursive =  TRUE)	
	
	setSecondaryStatus( IDsec=IDsec, status='BAM to SAM..', startTime=T )
	
	reg <- makeHtsflowRegistry(paste0("HF_MC1", IDsec))
	ids <- batchMap(reg, fun=bamToSam, values$primary_id, rep(SAMfolder, length(values$primary_id)))
	
	submitJobs(reg)
	
	waitForJobs(reg)
	
	showStatus(reg)
	
	errors<-findErrors(reg)
	
	if (length(errors) > 0) {
		##removeRegistry(reg,"no")
		stop(paste0(length(errors), " job(s) failed, exit"))
	}
	
	#removeRegistry(reg,"no")
	
	setSecondaryStatus( IDsec=IDsec, status='Deconvolute enrichment..' )
	
	reg <- makeHtsflowRegistry(paste0("HF_MC2", IDsec))
	ids <- batchMap(reg, fun=meth.call, files_location=rep(SAMfolder, length(values$primary_id)),
			output_folder=rep(outFolder, length(values$primary_id)),
			no_overlap=sapply( 1:length(values$primary_id), function(x) as.logical( as.numeric( values$no_overlap[x] ) )),
			read.context=sapply( 1:length(values$primary_id), function(x) values$read_context[x]	),
			Nproc=rep(1, length(values$primary_id))
	)
	submitJobs(reg)
	
	waitForJobs(reg)
	
	showStatus(reg)
	
	errors<-findErrors(reg)
	
	if (length(errors) > 0) {
		#removeRegistry(reg,"no")
		stop(paste0(length(errors), " job(s) failed, exit"))
	}
	
	#removeRegistry(reg,"no")
	
	deleteFile(SAMfolder, recursive=TRUE)
	
	setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
}



bamToSam <- function( BAMid, outFolder ) {
	cmd <- paste0(
			getHTSFlowPath("samtools")
			," view -h -o "
			,outFolder
			,BAMid
			,".sam "
			,getHTSFlowPath("HTSFLOW_ALN")
			,"/"
			,BAMid
			,".bam"
	)
	loginfo(cmd)
	return(system(cmd))
}

