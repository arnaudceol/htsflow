setwd("/data/BA/htsflow2_test/pipeline/")
source("commons/wrappers.R")
library(logging, quietly = TRUE)
basicConfig(level="DEBUG")

# load configuration
source("commons/config.R")
initHtsflow()



library("BatchJobs", quietly = TRUE)

waitAndPrint <- function( label ){
	setwd("/data/BA/htsflow2_test/pipeline/")
	loadConfig("BatchJobs.R")
	setwd("/home/aceol/")
	
	print(label)
	config <-getConfig()
	config["max.concurrent.jobs"] <- "Inf"
	setConfig(config)
	reg <- makeHtsflowRegistry(paste0("tc_", label))
	ids <- batchMap(reg, fun=print, 1:10)
	submitJobs(reg)
	removeRegistry(reg, ask="no")
}

setwd("/data/BA/htsflow2_test/pipeline/")
loadConfig("BatchJobs.R")
setwd("/home/aceol/")

## reg <- makeHtsflowRegistry("testchunks")
## 
## numJobs <- 95
## 
## chunkSize <- 10
## 
## numChunks = ceiling(numJobs/chunkSize)
## 
## print(numChunks)
config <-getConfig()
config["max.concurrent.jobs"] <- 5
setConfig(config)

removeRegistry(reg, ask="no")
reg <- makeHtsflowRegistry("testchunks")
ids <- batchMap(reg, fun=waitAndPrint, 1:20)
#resources <- as.list(c("-tc"="2"))
submitJobs(reg)
removeRegistry(reg, ask="no")

for (i in 1:numChunks) {
	start <- 1 + (i -1 ) * chunkSize
	end <- i * chunkSize
	subIds <- ids[start:end]
	print(paste(start, end, sep="-"))
	print(subIds)
	ids <- batchMap(reg, fun=waitAndPrint, subIds)
	submitJobs(reg)
	waitForJobs(reg)	
}
removeRegistry(reg)



#chunked = chunk(getJobIds(reg), n.chunks = 10, shuffle = FALSE)

submitJobs(reg, chunked)

waitForJobs(reg)

print("done")

