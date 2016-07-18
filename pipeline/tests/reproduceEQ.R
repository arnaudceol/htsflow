

source("commons/wrappers.R")
source("commons/dbFunctions.R")
source("commons/pipelineFunctions.R")
source("commons/geo.R")
source("commons/genomesConfig.R")

library(logging, quietly = TRUE)
basicConfig(level="INFO")

# load configuration
source("commons/config.R")
initHtsflow()


gitVersionFile<-"version"
if (! file.exists(gitVersionFile)){
	gitVersion <- "UNSPECIFIED!"
} else {
	gitVersion <- readChar(gitVersionFile, file.info(gitVersionFile)$size)
}
loginfo(paste0("HTSFLOW git version number: ", gitVersion))



# Add path variables:
Sys.setenv(PATH=paste(Sys.getenv("PATH"),getHTSFlowPath("bowtie_dir"),getHTSFlowPath("bowtie2_dir"),getHTSFlowPath("HTSFLOW_TOOLS"),getHTSFlowPath("tophat_dir"),sep=":"))

loginfo(Sys.getenv("PATH"))

# load all scripts for secondary analyses
sourceSecondaries <- sapply(list.files(pattern="[.]R$", path="secondary/", full.names=TRUE), source,simplify = FALSE)

source("primary.R")
source("secondary.R")
source("merging.R")
source("deletePrimary.R")
source("deleteSecondary.R")
source("geoDownload.R")

library("BatchJobs", quietly = TRUE)

IDsec=995
method = "expression_quantification"

values <- getSecondaryData(IDsec, method)

REFGENOME <- unique( unlist( sapply( values$primary_id, function(x) extractInfoFromDB( paste0('SELECT ref_genome FROM sample, primary_analysis WHERE sample.id = sample_id AND source = origin AND primary_analysis.id=',x ) ) ) ) )

library( org.Mm.eg.db , quietly = TRUE)
EG2GSlist <- as.list( org.Mm.egSYMBOL )

IDpreList <- values$primary_id

sampleNamesSQL <- paste0(
		"SELECT primary_analysis.id, sample_name FROM sample, primary_analysis, expression_quantification WHERE sample.id = sample_id AND source = origin AND primary_id = primary_analysis.id AND secondary_id ="
		,IDsec
		,";"
)

sampleNames <- extractInfoFromDB(sampleNamesSQL)
rownames(sampleNames) <- sampleNames$id

sampleNames[values$primary_id,]
ALNfold <- paste ( getHTSFlowPath("HTSFLOW_ALN"), "/", sep="" )
COUNTfold <- paste ( getHTSFlowPath("HTSFLOW_COUNT"), "/", sep="" )

countList <- sapply(1:length(IDpreList), function(x) paste(COUNTfold, IDpreList[x], ".count", sep="") )
TotReads <- sapply(IDpreList, function(x) {
			SQL <- paste(
					"SELECT reads_num FROM primary_analysis WHERE id="
					,x
					,";"
					,sep=""
			)
			readsAln <- unlist( extractInfoFromDB(SQL) )
		}
)
names(TotReads) <- NULL
TotReads <- as.numeric( as.vector(TotReads) )	
numGenes <- getNumberOfGenes(REFGENOME)
RPKMS <- sapply(1:length(countList), function(i) {
			tmp <- read.table(countList[i], header=T, nrows=numGenes, stringsAsFactors=FALSE, row.names=1)
			geneIDs <- rownames(tmp)
			reads <- tmp[,length(tmp)]
			lengths <- tmp[,length(tmp)-1]
			rpkms <- ( as.numeric(reads) / ( as.numeric(lengths) * as.numeric(TotReads[i]) ) ) *10^9
			names(rpkms) <- geneIDs
			rpkms
		})
RPKMS <- as.data.frame(RPKMS)

colnames(RPKMS) <- getColumnNames(IDpreList, sampleNames)

RPKMS <- RPKMS[names(EG2GSlist[rownames(RPKMS)][!sapply(EG2GSlist[rownames(RPKMS)], is.null)]),]
rownames(RPKMS) <- EG2GSlist[rownames(RPKMS)]
RPKMS <- RPKMS[order(rownames(RPKMS)),]
loginfo (head(RPKMS))
loginfo ( paste( "Saving RPKMS.rds in ", outFolder, sep="" ) )
saveRDS(RPKMS, file=paste(outFolder,"RPKMS.rds",sep=""))

# eRPKM	
numGenes <- getNumberOfGenes(REFGENOME)
eRPKMS <- sapply(1:length(countList), function(i) {
			tmp <- read.table(countList[i], header=T, nrows=numGenes, stringsAsFactors=FALSE, row.names=1)
			geneIDs <- rownames(tmp)
			reads <- tmp[,length(tmp)]
			eRPKMTotReads <- sum(tmp[,length(tmp)])
			lengths <- tmp[,length(tmp)-1]
			erpkms <- ( as.numeric(reads) / ( as.numeric(lengths) * as.numeric(eRPKMTotReads) ) ) *10^9
			names(erpkms) <- geneIDs
			erpkms
		})
eRPKMS <- as.data.frame(eRPKMS)

colnames(eRPKMS) <- getColumnNames(IDpreList, sampleNames)

eRPKMS <- eRPKMS[names(EG2GSlist[rownames(eRPKMS)][!sapply(EG2GSlist[rownames(eRPKMS)], is.null)]),]
rownames(eRPKMS) <- EG2GSlist[rownames(eRPKMS)]
eRPKMS <- eRPKMS[order(rownames(eRPKMS)),]
loginfo (head(eRPKMS))
loginfo ( paste( "Saving eRPKMS.rds in ", outFolder, sep="" ) )
saveRDS(eRPKMS, file=paste(outFolder,"eRPKMS.rds",sep=""))





A = matrix( 
		 c(2, 4, 3, 1, 5, 7), # the data elements 
		  nrow=2,              # number of rows 
		  ncol=3,              # number of columns 
		  byrow = TRUE) 