source("commons/config.R")
source("tests/function.R")

initHtsflow();
#print(paste("P1: ", getHTSFlowPath("HTSFLOW_PIPELINE")))

library("BatchJobs")

loadConfig("BatchJobs.R")


f <- function(x) {
	#a<-test(x)
	#initHtsflow();
	
	print(paste("P2: ", getHTSFlowPath("HTSFLOW_PIPELINE")))
	reg <- makeHtsflowRegistry(id="minimal2", src.dirs=paste0(getHTSFlowPath("HTSFLOW_PIPELINE"), "/commons/")) #, file.dir="/data/BA/public_html/htsflow2-data/htsflow-out/minimal", skip=FALSE)
	
	batchMap(reg, test, 1:2)
	
	## Submit jobs:
	submitJobs(reg)
	
	## Give jobs a chance to register as started and then show the job status:
#Sys.sleep(5)
	
	waitForJobs(reg)
	
	
	## removeRegistry(reg,"no")
	
}


f()