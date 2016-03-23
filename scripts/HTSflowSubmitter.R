library(logging, quietly = TRUE)
library(raster, quietly = TRUE)

basicConfig(level="DEBUG")

htsflowConfig <<- readIniFile(Sys.getenv("HTSFLOW_CONF"), aslist = TRUE)
pipelineDir <- htsflowConfig$htsflow[["HTSFLOW_PIPELINE"]]
setwd(pipelineDir)

source("commons/config.R")
initHtsflow()

source("commons/wrappers.R")
source("commons/dbFunctions.R")
source("commons/pipelineFunctions.R")
source("pipelineJob.R")

library("BatchJobs", quietly = TRUE)

#loadConfig("BatchJobsLocal.R")
loadConfig("BatchJobs.R")

userName <-Sys.getenv("USER")

userDir <- getUserDir() 

if (!dir.exists(userDir)) {
	createDir(userDir)
}


# lock 
lockFile <- paste0(userDir, "/", "/.lock")	

# If lock file exist, exit; if not create it 
if (file.exists(lockFile)) {
	quit(save = "no", status = 0, runLast = TRUE)
} else {
	loginfo(sprintf("Lock user folder: %s", file.create(lockFile)))
}

#sqlQuery = paste0("select job_list.id, analyses_type, analyses_id, job_list.action, job_list.created from users, job_list WHERE job_list.user_id= users.user_id AND launched is NULL AND user_name = '" , userName , "' ")
sqlQuery = paste0("select job_list.id, analyses_type, analyses_id, job_list.action, job_list.created from users, job_list WHERE job_list.user_id= users.user_id AND queued is NULL AND user_name = '" , userName , "'")

jobs = extractInfoFromDB(sqlQuery)

if (length(jobs$id) > 0) {
	
	setwd(getUserDir())

	for (i in 1:length(jobs$id)) {
		
		jobId<-jobs$id[i]
		analysisId<-jobs$analyses_id[i]
		type<-jobs$analyses_type[i]
		action<-jobs$action[i]
		
		id = getJobID(analysisId, type)
		
		regName <- paste0("HF_",id)
		
		jobDir<-paste0(getUserDir(), "/",id, "/")
		
		if (! file.exists(jobDir)) {
			createDir(jobDir, recursive=TRUE)
		}
		
		registryDir <- paste0(jobDir, "/registry")
		if (file.exists(registryDir)) {
			loginfo(sprintf("Remove run files: %s", deleteFile(registryDir, recursive=TRUE)))
		}
		
		
		setwd(jobDir)
		loginfo(sprintf("Work in : %s " , jobDir))
		reg <- makeRegistry(regName, work.dir=paste0(jobDir), file.dir= registryDir, sharding=FALSE)
		reg <- addRegistrySourceDirs(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/commons/"))
		reg <- addRegistrySourceDirs(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/secondary/"))
		reg <- addRegistrySourceFiles(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/primary.R"))
		reg <- addRegistrySourceFiles(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/deletePrimary.R"))
		reg <- addRegistrySourceFiles(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/secondary.R"))
		reg <- addRegistrySourceFiles(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/deleteSecondary.R"))
		reg <- addRegistrySourceFiles(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/merging.R"))
		reg <- addRegistrySourceFiles(reg, paste0(getHTSFlowPath("HTSFLOW_PIPELINE"),"/geoDownload.R"))
		reg <- addRegistryPackages(reg, "logging")
		
		ids <- batchMap(reg, use.names = TRUE, fun=pipeline, analysisId, type, action)
		#jobs$analyses_id
		#getJobID
		setJobNames(reg, getJobIds(reg), jobnames = sapply(analysisId, function(x) paste0("PROUT", x)))
		
		# mark as done in DB
		sqlQuery = paste0("UPDATE job_list SET queued = NOW() WHERE id = '", jobId,"'" )	 
		updateInfoOnDB(sqlQuery)
		
		#submit
		submitJobs(reg)
		
		loginfo(sprintf("jobs ids: %s" , paste(sapply(getJobIds(reg), function(x) getJobInfo(reg, x)[['batch.id']]), sep=",", collapse=", ")))
	}
	
	
	
}
loginfo(sprintf("Unlock user folder: %s", file.remove(lockFile)))

