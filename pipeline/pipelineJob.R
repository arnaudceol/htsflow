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

# Put this pipeline in a function in order to launch it as a job
options(scipen=999)

source("commons/wrappers.R")
## source("commons/dbFunctions.R")
## source("commons/pipelineFunctions.R")
##
##
## library(logging, quietly = TRUE)
## basicConfig(level="DEBUG")
##
## # load configuration
## source("commons/config.R")
## sourceSecondaries <- sapply(list.files(pattern="[.]R$", path="secondary/", full.names=TRUE), source,simplify = FALSE)
##
## source("primary.R")
## source("secondary.R")
## source("merging.R")
## source("deletePrimary.R")
## source("deleteSecondary.R")

pipeline <- function(id, TypeOfAnalysis, action, user_name) {
	
	basicConfig(level="DEBUG")
	loginfo("start")
	
	initHtsflow()
	
	# Add path variables:
	Sys.setenv(PATH=paste(Sys.getenv("PATH"),getHTSFlowPath("bowtie_dir"),getHTSFlowPath("bowtie2_dir"),getHTSFlowPath("HTSFLOW_TOOLS"),getHTSFlowPath("tophat_dir"),sep=":"))
	
	loginfo(paste("Path: " , Sys.getenv("PATH")))
	
	# load all scripts for secondary analyses
	#library("BatchJobs", quietly = TRUE)
	
	assign("PIPELINE_ID", id, envir=globalenv())
	if ( TypeOfAnalysis == "primary" ) {
		assign("PIPELINE_TYPE", "primary", envir=globalenv())
	} else if ( TypeOfAnalysis == "secondary" ) {
		assign("PIPELINE_TYPE", "secondary", envir=globalenv())
	} else if ( TypeOfAnalysis == "merging" ) {
		assign("PIPELINE_TYPE", "merging", envir=globalenv())
	} else if ( TypeOfAnalysis == "other" ) {
		assign("PIPELINE_TYPE", "other", envir=globalenv())
	}
	
	# Create default directories
	if (! file.exists(getPreprocessDir())){
		loginfo(paste("create directory: ", getPreprocessDir()))
		createDir(getPreprocessDir())		
	}
	
	if (! file.exists(getHTSFlowPath("HTSFLOW_SECONDARY"))){
		loginfo(paste("create directory: ", getHTSFlowPath("HTSFLOW_SECONDARY")))
		createDir(getHTSFlowPath("HTSFLOW_SECONDARY"))		
	}
	
	if (! file.exists(getHTSFlowPath("HTSFLOW_ALN"))){
		loginfo(paste("create directory: ", getHTSFlowPath("HTSFLOW_ALN")))
		createDir(getHTSFlowPath("HTSFLOW_ALN"))		
	}
	
	if (! file.exists(getHTSFlowPath("HTSFLOW_PRIMARY"))){
		loginfo(paste("create directory: ", getHTSFlowPath("HTSFLOW_PRIMARY")))
		createDir(getHTSFlowPath("HTSFLOW_PRIMARY"))		
	}
	
	if (! file.exists(getHTSFlowPath("HTSFLOW_COUNT"))){
		loginfo(paste("create directory: ", getHTSFlowPath("HTSFLOW_COUNT")))
		createDir(getHTSFlowPath("HTSFLOW_COUNT"))		
	}
	
	if (! file.exists(getHTSFlowPath("HTSFLOW_QC"))){
		loginfo(paste("create directory: ", getHTSFlowPath("HTSFLOW_QC")))
		createDir(getHTSFlowPath("HTSFLOW_QC"))		
	}
	
	
	
	# Go to user dir
	setUserWorkDir(user_name)
	
	 # Check if this job can be run: the combination of id/type/action should be in the job_list table and launch should be null
	 sqlCheck <- paste0("SELECT count(*) FROM job_list WHERE analyses_type = '",TypeOfAnalysis,"' AND analyses_id = '",id,"' AND action = '",action,"' AND started is null;")
	 jobRowCount <- as.numeric(extractSingleColumnFromDB(sqlCheck))
	 if (jobRowCount == 0) {
	     loginfo("This job does not exist or has already been run.")
	     stop("This job does not exist or has already been run.")
	 }
	
	 # Mark the job as started
	 sqlStart <- paste0("UPDATE job_list SET started = now() WHERE analyses_type = '",TypeOfAnalysis,"' AND analyses_id = '",id,"' AND action = '",action,"' AND started is null;")
	 dbQuery(sqlStart)
	
	
	 # Remove  BatchJobs repositories from previous sessions:
	 deleteFile("HF_*-files/", recursive=TRUE)
	
	
	 if ( TypeOfAnalysis == "primary") {
	     if (action == 'delete') {
	         tryCatch(
	                 deletePrimary(id) ,
	                 error = function(e)
	                 {
	                     print(e)
	                     loginfo("Session information: ")
	                     sessionInfo()
	                     setError( "Delete primary analysis pipeline exited with errors." )
	                     stop()
	                 }
	         )
	
	     } else {
	
	         setStatus(id, "primary", status="started", startTime=TRUE)
	
	         SQL <- paste0(
	                 "SELECT primary_analysis.*, pa_options.*, users.*,
	                         seq_method,
	                         CASE WHEN origin = 0 THEN 'LIMS' WHEN origin = 2 THEN 'EXTERNAL' END AS source,
	                         sample_name,
	                         reads_mode,
	                         genome,
	                         raw_data_path
	                         FROM sample, primary_analysis, pa_options, users
	                         WHERE sample.id = sample_id  AND source = origin  AND pa_options.id = options_id and primary_analysis.user_id = users.user_id and primary_analysis.id="
	                 ,id
	         )
	         flags <- extractInfoFromDB( SQL )
	         sample <- flags$raw_data_path # sample path
	         names(sample) <- id
	         genomePaths <- getGenomePaths( flags$genome )
	         tryCatch(
	                 primaryPipeline( sample, flags, genomePaths ) ,
	                 error = function(e)
	                 {
	                     print(e)
	                     loginfo("Session information: ")
	                     sessionInfo()
	                     setError( "Primary analysis pipeline exited with errors." )
	                     stop()
	                 }
	         )
	     }
	 } else if ( TypeOfAnalysis == "secondary" ) {
	     if (action == 'delete') {
	         tryCatch(
	                 deleteSecondary(id) ,
	                 error = function(e)
	                 {
	                     print(e)
	                     loginfo("Session information: ")
	                     sessionInfo()
	                     setError( "Delete secondary analysis pipeline exited with errors." )
	                     stop()
	                 }
	         )
	
	     } else {
	         setStatus(id, "secondary", status="started", startTime=TRUE)
	
	         tryCatch(
	                 secondaryPipeline( id ),
	                 error = function(e)
	                 {
	                     print(e)
	                     loginfo("Session information: ")
	                     sessionInfo()
	                     setError("Secondary analysis pipeline exited with errors." )
	                     stop()
	                 }
	         )
	     }
	 } else if ( TypeOfAnalysis == "merging" ) {
	
	     setStatus(id, "primary", status="started", startTime=TRUE)
	
	     flags <- getFlagsElementsFromMergeDB ( id )
	     SQL <- paste0("SELECT * FROM primary_analysis, pa_options WHERE options_id = pa_options.id and primary_analysis.id =",id)
	     flagsPRE <- extractInfoFromDB( SQL )
	     tryCatch(
	             merging ( flags, flagsPRE, id ),
	             error = function(e)
	             {
	                 print(e)
	                 loginfo("Session information: ")
	                 sessionInfo()
	                 setError( "Merging pipeline exited with errors." )
	                 stop()
	             }
	     )
	 } else if ( TypeOfAnalysis == "other" ) {
	
		setStatus(id, "other", status="started", startTime=TRUE)
	
		## SQL <- paste0("SELECT * FROM misc_task WHERE id =",id)
		## flags <- extractInfoFromDB( SQL )	
		## description <- flags$description
		tryCatch(			
			geoDownload ( id ),
			error = function(e)
			{
				print(e)
				loginfo("Session information: ")
				sessionInfo()
				setError( "GEO download exited with errors." )
				stop()
			}
		)
	}
	
	loginfo("Session information: ")
	sessionInfo()
}