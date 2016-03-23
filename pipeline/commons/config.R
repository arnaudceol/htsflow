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

library("raster", quietly = TRUE)
library(logging, quietly = TRUE)


getHTSFlowPath <- function(name) {
	
	if (!exists("htsflowConfig")) {
		initHtsflow();
	}
	
	value <- htsflowConfig$htsflow[[name]]
	txt <- c(value)
	i <-grep("\\[(.*)\\].*", txt, value=TRUE, perl=TRUE)
	if (length(i)) {
		type=sub("\\[(.*)\\].*", "\\1", value,perl=TRUE)
		value=sub("\\[.*\\](.*)", paste0(htsflowConfig$htsflow[type], "\\1"), value,perl=TRUE)
	}	
	return(value)
}


initHtsflow <- function() {
	
	basicConfig()

	# Only the group can read/write the directories
	Sys.umask(mode="0002")

	# Configuration file, including the directories where the files and executables are stored
	if(TRUE) { #//! exists("getHTSFlowPath", mode = "function")) {	
		loginfo(paste0("HTS-flow conf: " , Sys.getenv("HTSFLOW_CONF")))
		htsflowConfig <<- readIniFile(Sys.getenv("HTSFLOW_CONF"), aslist = TRUE)
	} 		

	# Global options:
	options(BatchJobs.load.config = FALSE)
	options(BatchJobs.verbose = FALSE)
	options(BBmisc.ProgressBar.style = "off")	
	
}


