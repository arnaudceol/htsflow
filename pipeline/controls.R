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
source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"), "/commons/dbFunctions.R"))
source(paste0(getHTSFlowPath("HTSFLOW_PIPELINE"), "/commons/pipelineFunctions.R"))

##########################################################
SQL <- "SELECT * FROM paths"
paths <- extractInfoFromDB( SQL )
##########################################################
ToAid <- commandArgs(TRUE)[1]
TypeOfAnalysis <- commandArgs(TRUE)[2]
TypeOfAction <- commandArgs(TRUE)[3] # either delete, redo
##########################################################

if ( TypeOfAnalysis == "primary" ) {
	SQL <- paste0(
        "SELECT primary_analysis.*, pa_options.*, users.*,
            seq_method,
            CASE WHEN origin = 0 THEN 'LIMS' WHEN origin = 2 THEN 'EXTERNAL' END AS source,
            sample_name,
            reads_mode,
            ref_genome,
            raw_data_path
          FROM primary_analysis, pa_options, users, sample
		  WHERE sample.source <> 1 AND sample.id = sample_id AND source = origin  AND pa_options.id = options_id and primary_analysis.user_id = users.user_id and primary_analysis.id="
        ,ToAid
    )
	flags <- extractInfoFromDB( SQL )
	if (TypeOfAction == 'redo') {
			# # to restart an analysis we have to remove the job if it is yet running
			# # after that we can write in the user-file the commands for rerunning
			# # the job.
			BAMfolder <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"), "/" )
			REDOFILE <- paste0( getHTSFlowPath("HTSFLOW_OUTPUT")
							,"redo_"
							,ToAid
							,"_primary.done"
							)			
	}
} else if ( TypeOfAnalysis == "secondary" ) {
	# # TO BE MODIFIED
	flags <- getFlagsElementsFromSecondaryDB( ToAid )
} else if ( TypeOfAnalysis == "merging" ) {
	flags <- getFlagsElementsFromMergeDB ( ToAid )
	SQL <- paste0(
        "SELECT * FROM primary_analysis, pa_options WHERE pa_options.id = options_id and primary_analysis.id="
        ,flags$id_pre_fk
    )
    flagsPRE <- extractInfoFromDB( SQL )
}


