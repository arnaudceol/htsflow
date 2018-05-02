# Install R libraries

#, 'pROC', 'deSolve', 'rootSolve', 'preprocessCore')
install.packages(packagesList, repos="http://cran.us.r-project.org" )

source("https://bioconductor.org/biocLite.R")
biocLite()


# Install the right version
install.packages("BiocInstaller", 
		repos="http://bioconductor.org/packages/3.1/bioc")

library(BiocInstaller)

packagesList <- c('RColorBrewer', 'gplots', 'BatchJobs', 'raster', 'logging', 'R.utils')
biocLite(packagesList)
biocliteList <- c('compEpiTools','DESeq2', 'GEOmetadb','SRAdb', 'INSPEcT', 'TxDb.Mmusculus.UCSC.mm9.knownGene', 'TxDb.Hsapiens.UCSC.hg19.knownGene', 'org.Mm.eg.db', 'org.Hs.eg.db')
biocLite(biocliteList)

biocliteList <- c('compEpiTools','DESeq2', 'TxDb.Mmusculus.UCSC.mm9.knownGene','TxDb.Mmusculus.UCSC.mm10.knownGene', 'TxDb.Hsapiens.UCSC.hg19.knownGene','TxDb.Hsapiens.UCSC.hg18.knownGene', 'org.Mm.eg.db', 'org.Hs.eg.db')
biocLite(biocliteList)


#biocliteList <- c('compEpiTools','DESeq2', 'GEOmetadb','SRAdb')
#biocLite(biocliteList, type="source")

biocLite('compEpiTools', type="source", dependencies=TRUE)
biocLite('DESeq2', type="source", dependencies=TRUE)
biocLite('preprocessCore')
#biocLite('GEOmetadb', dependencies=TRUE)
#biocLite('SRAdb', dependencies=TRUE)
	
install.packages("devtools", repos="http://cran.us.r-project.org")
devtools::install_git("git://github.com/seandavi/GEOmetadb.git")
devtools::install_git("git://github.com/seandavi/SRAdb.git")

