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

# Functions that wrape another function with loggin or other actions

library(logging)

###################################################################################
# System commands
tryOrExit <- function( command, label ) {
	loginfo(command)
	result <- system(command)
	if (result > 0) {
		setError(label)
		loginfo("Session information: ")		
		traceback()
		sessionInfo()
		stop(label)
	}
	return(result)
}

tryInternOrExit <- function( command, label ) {
	loginfo(command)
	
	tryCatch(
			result <- system(command,  intern=T),
			error = function(e)
			{
				logerror("Command failed: %",  command)			
				setError(label)
				sessionInfo()		
				stop(label)
			}
	)
	return(result)
}

###################################################################################
# File commands
deleteFile <- function(filename, recursive=FALSE) {
	if (recursive) {
		loginfo(paste("rm -r ", filename))
	} else {
		loginfo(paste("rm ", filename))
	}
	
	result <- unlink(filename, recursive=recursive)
	
	if (result > 0) {
		setError(paste("Cannot remove file/directory: ", filename))
		loginfo("Session information: ")		
		traceback()
		sessionInfo()
		stop(paste("Cannot remove file/directory: ", filename))
	}
	
	return(result)
}

createDir <- function(filename, recursive=FALSE) {
	
	if (! file.exists(filename)) {
		
		if (recursive) {
			loginfo(paste("mkdir -p ", filename))
		} else {
			loginfo(paste("mkdir ", filename))
		}
		
		result <- dir.create(filename, recursive=recursive, showWarnings = FALSE )
		
		if (result != TRUE) {
			setError(paste("Cannot create directory: ", filename))
			loginfo("Session information: ")		
			traceback()
			sessionInfo()
			stop(paste("Cannot create directory: ", filename))
		}
		
		return(result)
	} else {
		return(0)
	}
	
}


###################################################################################
# Cluter commands

# Add common source dir and library to a registry
makeHtsflowRegistry <- function(regName) {
	reg <- makeRegistry(regName)
	reg<-addRegistrySourceDirs(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/commons/"))
	reg<-addRegistrySourceDirs(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/secondary/"))
	reg<-addRegistryPackages(reg, "logging")
	reg<-addRegistryPackages(reg, "BatchJobs")
	reg<-addRegistryPackages(reg, "GenomicRanges")
	reg<-addRegistryPackages(reg, "compEpiTools")
	reg<-addRegistryPackages(reg, "methylPipe")
	return(reg)
}


