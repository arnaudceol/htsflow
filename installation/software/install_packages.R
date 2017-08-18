# Install R libraries

# Install the right version
install.packages("BiocInstaller", 
		repos="http://bioconductor.org/packages/3.1/bioc")

library(BiocInstaller)

biocliteList <- c('compEpiTools','DESeq2', 'GEOmetadb','SRAdb')

biocLite(biocliteList, dependencies=TRUE)

biocLite('compEpiTools', dependencies=TRUE)
biocLite('DESeq2', dependencies=TRUE)
biocLite('GEOmetadb', dependencies=TRUE)
biocLite('SRAdb', dependencies=TRUE)


packagesList <- c('RColorBrewer', 'gplots', 'BatchJobs', 'raster', 'logging', 'R.utils')
install.packages(packagesList, repos="http://cran.us.r-project.org" )

# inspect
packagesList <- c('pROC', 'deSolve', 'rootSolve', 'preprocessCore')
install.packages(packagesList, repos="http://cran.us.r-project.org" )
