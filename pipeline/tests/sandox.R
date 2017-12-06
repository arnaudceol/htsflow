

source("commons/wrappers.R")                                           
source("commons/dbFunctions.R")                                        
source("commons/pipelineFunctions.R")                                  
source("commons/geo.R")                                              
source("commons/genomesConfig.R")                      
source("commons/bigwig.R")                                             

library(logging, quietly = TRUE)                                       
basicConfig(level="INFO")                                              
source("commons/config.R")                                             
initHtsflow()                                                          
loginfo(Sys.getenv("PATH"))                                            

# load all scripts for secondary analyses                              
