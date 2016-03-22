# Install R libraries

source("http://bioconductor.org/biocLite.R")
#update.packages() 


biocliteList <- c(
'org.Mm.eg.db',
'org.Hs.eg.db',
'org.Dm.eg.db',
'TxDb.Mmusculus.UCSC.mm9.knownGene',
'TxDb.Hsapiens.UCSC.hg19.knownGene',
'TxDb.Mmusculus.UCSC.mm10.knownGene',
'TxDb.Rnorvegicus.UCSC.rn5.refGene',
'compEpiTools',
'DESeq2',
'GEOmetadb',
'SRAdb')

biocLite(biocliteList)

packagesList <- c('RColorBrewer', 'gplots', 'BatchJobs', 'raster', 'logging')
install.packages(packagesList, repos="http://cran.us.r-project.org" )

