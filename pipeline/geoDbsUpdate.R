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

options(scipen=999)

source("commons/wrappers.R")
source("commons/pipelineFunctions.R")

library(logging, quietly = TRUE)
basicConfig(level="DEBUG")

library('GEOmetadb', quietly = TRUE)
library('SRAdb', quietly = TRUE)

# load configuration
source("commons/config.R")
initHtsflow()

downloadDate <-format(Sys.time(), "%a %b %d %X %Y")



geoDbPath <- getHTSFlowPath('HTSFLOW_GEODB')

dateFile <- paste0(geoDbPath, "/", "download_date.txt")

if (file.exists(dateFile)) {
	file.remove(dateFile)
}

loginfo(paste0("GEO DB download dir: " , geoDbPath))		

if (! file.exists(geoDbPath)) {
	
	result <- dir.create(geoDbPath, recursive=TRUE )
	
	if (result != TRUE) {
		loginfo("Session information: ")		
		traceback()
		sessionInfo()
		stop(paste("Cannot create directory: ", geoDbPath))
	}
} 



getSRAdbFile(destdir=geoDbPath)
getSQLiteFile(destdir=geoDbPath)


fileConn<-file(dateFile)
writeLines(downloadDate, fileConn)
close(fileConn)





