library("BatchJobs")

reg <- makeRegistry("TestReg")


#############################################################

mysummary <- function(x, y) {	
	if (x <= 2) {		
		print(paste(x, y))	
	} else {
		stop("Bigger than 2!")
	}
}

#options(BatchJobs.load.config = FALSE)
#options(BatchJobs.verbose = FALSE)

#cluster.function <- makeClusterFunctionsSGE(job_test_batchjob.tmlp, list.jobs.cmd = c("qstat", "-u $USER"))

#removeRegistry(TestReg,"no")

ids<-batchMap(reg, fun=mysummary, y=c(2,2,0,1,1,1), x=c(1,2,3,1,4,1))

submitJobs(reg)

status <- getStatus(reg)

showStatus(reg)

errors<-findErrors(reg)

if (length(errors) > 0) {
	stop(paste0(length(error), " job(s) failed, exit"))
}


removeRegistry(reg,"no")


res <- reduceResultsMatrix(reg)


