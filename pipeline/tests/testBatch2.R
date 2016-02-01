source("config.R")

library("BatchJobs")

loadConfig("BatchJobs.R")

reg <- makeRegistry(id="minimal") #, file.dir="/data/BA/public_html/htsflow2-data/htsflow-out/minimal", skip=FALSE)

batchMap(reg, f, 1:10)

## Submit jobs:
submitJobs(reg)

## Give jobs a chance to register as started and then show the job status:
#Sys.sleep(5)

waitForJobs(reg)


## removeRegistry(reg,"no")