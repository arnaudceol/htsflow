   
tmpDir<-"/data/BA/htsflowNP/data/genomes/tmp"
genomesDir<-"/data/BA/htsflowNP/genomes/"

species <- "Homo_sapiens"
host <- "UCSC"
version <- "hg19"
txdbLib <- "TxDb.Hsapiens.UCSC.hg19.knownGene"	
annotationLibName <- "org.Hs.eg.db knownGene"
tableName <- "org.Hs.egSYMBOL"


print(paste0("Download version: ", version, " of genome ", species, " lib: " , txdbLib))

versionDir <- paste0(genomesDir, "/", version)
versionGtfDir <- paste0(genomesDir, "/", version, "/GTF")
