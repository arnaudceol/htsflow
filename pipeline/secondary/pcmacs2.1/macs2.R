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

library(logging, quietly = TRUE)

macs2Exec <- function( INPUT_BAM, CHIP_BAM, label, pvalue, stats, macsOUT, REFGENOME, peakSHAPE ) {
	
	# load config and common functions
	workdir <- getwd()
	setwd(getHTSFlowPath("HTSFLOW_PIPELINE"))
	
	loadConfig("BatchJobs.R")
	setwd(workdir)
	
	# delete directory if it exists
	if (! file.exists(macsOUT)) {
		createDir(macsOUT,  recursive =  TRUE)
	} else {
		loginfo(paste("Directory ", macsOUT, "already exists."))
	}
		
	# REFGENOME IS USED FOR macs2 size of the genome. In rattus we use mouse that is very close in size.
	if ( REFGENOME == 'mm9' || REFGENOME == 'mm10' ) {
		gg <- 'mm'
	}
	if ( REFGENOME == 'hg18' || REFGENOME == 'hg19' ) {
		gg <- 'hs'
	}
	if ( REFGENOME == 'dm6' ) {
		gg <- 'dm'
	}
	if ( REFGENOME == 'rn5' ) {
		gg <- 'mm'
	}
	
	
	if (INPUT_BAM == CHIP_BAM) {
		loginfo("Input and ChIP are the same, ignore input.")
		inputOption <- ""
	} else {
		inputOption <- paste0(' -c ', INPUT_BAM)
	}
	
	if ( peakSHAPE == 'NARROW' ) {
		cmd <- paste0(
				getHTSFlowPath("macs2")
				,' callpeak -t '
				,CHIP_BAM
				,inputOption
				,' --name="'
				,macsOUT
				,label
				,'"'
				,' -p '
				,pvalue
				,' -g '
				,gg
				,' '
				,stats
		)
	} else {
		cmd <- paste0(
				getHTSFlowPath("macs2")
				,' callpeak -t '
				,CHIP_BAM
				,inputOption
				,' --name="'
				,macsOUT
				,label
				,'"'
				,' -p '
				,pvalue
				,' --broad '
				,' -g '
				,gg
				,' '
				,stats
		)
	}
	
	loginfo ( cmd )
	system( cmd, intern=T )
}