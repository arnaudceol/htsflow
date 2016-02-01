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


source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/commons/config.R"))
library("BatchJobs", quietly = TRUE)

source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"), "/commons/dbFunctions.R"))
source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"), "/commons/pipelineFunctions.R"))

source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"), "/primary.R"))

SQL <- paste0("SELECT id FROM primary_analysis WHERE raw_reads_num IS NULL;")
IDs <- extractInfoFromDB( SQL )
IDs <- unlist(IDs)
names(IDs) <- NULL

print (IDs)
## Let's load all the paths to tools needed by HTS-flow
#paths <- HTSFLOW_Path()

passo <- 15

fixHTS <- function( ID ) {
		SQL <- paste0(
	        "SELECT primary_analysis.*,  pa_options.*, users.*,
               seq_method,
               CASE WHEN origin = 0 THEN 'LIMS' WHEN origin = 2 THEN 'EXTERNAL' END AS source,
               sample_name,
               reads_mode,
               ref_genome,
               raw_data_path
             FROM primary_analysis, pa_options, users, sample WHERE sample.source <> 1 AND id_sample = sample.id AND source = origin  AND options_id = pa_options.id and users.user_id = primary_analysis.user_id and primary_analysis="
	        ,ID
	    )
		flags <- extractInfoFromDB( SQL )
		sample <- flags$raw_data_path # sample path
		names(sample) <- ID
		genomePaths <- getGenomePaths( flags$ref_genome )

		sampleName <- names(sample)
	    bamFileName <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"), "/", sampleName, ".bam" )
	    # location of files important for the primary analysis ############
	    outPath <-  getPreprocessDir()

	    SQL <- paste0( 'SELECT raw_reads_num FROM primary_analysis where id=', ID )
	    rawnum <- extractInfoFromDB( SQL )

		## update the DB with status 'quality control'
		SQL <-
		paste(
		    "UPDATE primary_analysis SET status='quality control on reads..' WHERE id="
		    ,sampleName
		    ,sep=""
		)
		res <- updateInfoOnDB( SQL )
	    ## qualityCheckOnReads FUNCTION EXTRACTS THE READS, REMOVE THE BAD READS BASED ON QUALITY
	    ## TRIMMING AND MASKING
	    qualityCheckOnReads( flags, sample, outPath )
		system ( paste0 ( "rm -f ", outPath, sampleName , "*" ) )
	    SQL <- paste("UPDATE primary_analysis SET status='FastQC quality control..' WHERE id=", sampleName ,sep="")
	    res <- updateInfoOnDB( SQL )
	    fastQCexec( sample )
	    ## Finally let's update the status with 'completed'.
	    SQL <- paste("UPDATE primary_analysis SET status='completed' WHERE id=", sampleName ,sep="")
	    res <- updateInfoOnDB( SQL )

}

listIDs <- split(IDs, ceiling(seq_along(IDs)/20))

# load config and common functions
workdir <- getwd()
setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))

loadConfig("BatchJobs.R")
setwd(workdir)


reg <- makeHtsflowRegistry("HF_StdPlot")
ids <- batchMap(reg, fun=fixHTS, listIDs)
submitJobs(reg)

waitForJobs(reg)
