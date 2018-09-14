# Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.


# Download and prepare genomes

options(scipen=999)

source("commons/wrappers.R")
source("commons/dbFunctions.R")
source("commons/pipelineFunctions.R")

library(logging, quietly = TRUE)
basicConfig(level="DEBUG")

source("http://bioconductor.org/biocLite.R")
library(GenomicFeatures)
library(compEpiTools)

# load configuration
source("commons/config.R")
initHtsflow()

# Add path variables:
Sys.setenv(PATH=paste(Sys.getenv("PATH"),getHTSFlowPath("bowtie_dir"),getHTSFlowPath("bowtie2_dir"),getHTSFlowPath("HTSFLOW_TOOLS"),getHTSFlowPath("tophat_dir"),sep=":"))

loginfo(Sys.getenv("PATH"))

genomeListFile <-commandArgs(TRUE)[1]

genomesDir <- getHTSFlowPath("HTSFLOW_GENOMES")
tmpDir <-  paste0(genomesDir, "/tmp")

if (! file.exists(tmpDir)) {
	result <- dir.create(tmpDir, recursive=TRUE )
	
	if (result != TRUE) {
		traceback()
		sessionInfo()
		stop(paste("Cannot create directory: ", tmpDir))
	}
}

genomes <- read.table(genomeListFile ,header=TRUE, sep=" ")


annotationsUrl <- biocinstallRepos()['BioCann']
bioclitePackages <- available.packages(contriburl = contrib.url(annotationsUrl))[,'Package']

downloadGenome <- function(genome) {
	# Homo_sapiens UCSC hg19 TxDb.Hsapiens.UCSC.hg19.knownGene org.Hs.eg.db knownGene org.Hs.egSYMBOL
	species <- genome[1]
	host <- genome[2]
	version <- genome[3]
	txdbLib <- genome[4]	
	annotationLibName <- genome[5]
	tableName <- genome[6]
	
	print(paste0("Download version: ", version, " of genome ", species, " lib: " , txdbLib))
	
	versionDir <- paste0(genomesDir, "/", version)
	versionGtfDir <- paste0(genomesDir, "/", version, "/GTF")
	#versionBsDir <- paste0(genomesDir, "/", version, "_bs")
	
	dir.create(versionDir, recursive=TRUE, showWarnings = FALSE )
	dir.create(versionGtfDir, recursive=TRUE, showWarnings = FALSE )
	#dir.create(versionBsDir, recursive=TRUE, showWarnings = FALSE )
	
	igenomeFile <- paste0(species, "_", host,"_", version, ".tar.gz")
	
	if (! file.exists(paste(tmpDir, igenomeFile, sep="/"))) {
		 download.file(paste("ftp://igenome:G3nom3s4u@ussd-ftp.illumina.com", species, host, version, igenomeFile, sep="/"), 
	         destfile=paste(tmpDir, igenomeFile, sep="/"), quiet=TRUE)	 
		 untar(paste(tmpDir, igenomeFile, sep="/"), exdir=tmpDir)
	}
	loginfo("igenome files ready.")
	# Copy files
	file.copy(from = paste( tmpDir, species, host, version, "Sequence", "WholeGenomeFasta", "genome.fa", sep="/"), to = versionDir)
	
	flist <- list.files(paste( tmpDir, species, host, version, "Sequence", "WholeGenomeFasta", sep="/"), "^.*\\.fa.*$", full.names = TRUE)
	file.copy(flist, versionDir)
	
	flist <- list.files(paste( tmpDir, species, host, version, "Sequence", "Bowtie2Index", sep="/"), "^.*\\.*$", full.names = TRUE)
	file.copy(flist, versionDir)
	
	flist <- list.files(paste( tmpDir, species, host, version, "Sequence", "BWAIndex", sep="/"), "^.*\\.*$", full.names = TRUE)
	file.copy(flist, versionDir)
	
	# rename
	flist<-list.files(versionDir,pattern="genome")
	
	
	
	sapply(flist,FUN=function(eachPath){
				file.rename(from=paste(versionDir,eachPath, sep="/"),to=sub(pattern=paste(version, "genome", sep="/") ,replacement=paste(version, version, sep="/"),paste(versionDir,eachPath, sep="/")))				
			})
		
		#Function to create txdb when this is not available in bioconductor
		if (txdbLib %in% bioclitePackages) {
			loginfo("Install TxDb from bioconductor and generate GTF with makeGtfFromDb")
			biocLite(txdbLib)
			library(txdbLib, character.only = TRUE)
			txdb=get(txdbLib)
			makeGtfFromDb(txdb, 'gene', filename=paste(versionGtfDir, "genes.gtf", sep="/"))
		} else if (host == 'UCSC') {
			loginfo(paste0("Create TxDb from makeTxDbPackageFromUCSC, install the library and generate GTF with makeGtfFromDb, tablename: ", tableName, ", version: ", version, ", to ", tmpDir ))
			txdb <- makeTxDbFromUCSC(genome=version, tablename="refGene", url="http://genome.ucsc.edu/cgi-bin/")
			makeTxDbPackage(txdb = txdb, version = "1.0.0", author='htsflow', maintainer='htsflow <htsflow@htsflow.org>', destDir=paste(tmpDir, sep="/"), pkgname=txdbLib)
                        install.packages(paste(tmpDir, txdbLib, sep="/"), repos = NULL)
            library(txdbLib, character.only = TRUE)			
			txdb=get(txdbLib)
			makeGtfFromDb(txdb, 'gene', filename=paste(versionGtfDir, "genes.gtf", sep="/"))
		} else if (host == 'Ensembl') {					
			loginfo(paste0("Create TxDb from the GTF file provided by ensembl, lib: ", txdbLib, ", dir: ", tmpDir))
			loginfo(paste0("copy ", paste( tmpDir, species, host, version, "Annotation", "Genes", "genes.gtf", sep="/") , " to " , versionGtfDir))
			file.copy(from = paste( tmpDir, species, host, version, "Annotation", "Genes", "genes.gtf", sep="/"), to = versionGtfDir)	
			txdb <- makeTxDbFromGFF(paste(versionGtfDir, "genes.gtf", sep="/"), format="gtf", organism=gsub("_", " ", species))
			makeTxDbPackage(txdb = txdb, version = "1.0.0", author='htsflow', maintainer='htsflow <htsflow@htsflow.org>', destDir=paste(tmpDir, sep="/"), pkgname=txdbLib )			
			install.packages(paste(tmpDir, txdbLib, sep="/"), repos = NULL)		
		} else if (host == 'gencode') {					
			loginfo(paste0("Create TxDb from the GTF file provided by ensembl, lib: ", txdbLib, ", dir: ", tmpDir))
			loginfo(paste0("copy ", paste( tmpDir, species, host, version, "Annotation", "Genes", "genes.gtf", sep="/") , " to " , versionGtfDir))
			file.copy(from = paste( tmpDir, species, host, version, "Annotation", "Genes", "genes.gtf", sep="/"), to = versionGtfDir)	
			txdb <- makeTxDbFromGFF(paste(versionGtfDir, "genes.gtf", sep="/"), format="gtf", organism=gsub("_", " ", species))
			makeTxDbPackage(txdb = txdb, version = "1.0.0", author='htsflow', maintainer='htsflow <htsflow@htsflow.org>', destDir=paste(tmpDir, sep="/"), pkgname=txdbLib )			
			install.packages(paste(tmpDir, txdbLib, sep="/"), repos = NULL)		
		} else {
			loginfo(paste0("The host ", host, " is not supported."))
			stop(paste0("The host ", host, " is not supported."))
		}
		
		# install annotations
		loginfo(paste0("Download and install ", annotationLibName)) 
		biocLite(annotationLibName)
		
		library(txdbLib, character.only=TRUE)
		txdb=get(txdbLib)
		
		# Create GTF
		loginfo("prepare transcripts.gtf")
		makeGtfFromDb(txdb, 'tx', filename=paste(versionGtfDir, "transcripts.gtf", sep="/"))	
				
		# create chrom.size
		fai<- read.csv(paste0(versionDir, "/", version, ".fa.fai"), header = FALSE, sep = "\t", colClasses=c( "character", "integer", "NULL", "NULL", "NULL") )
		write.table(fai, paste0(versionDir, "/", version, ".chrom.sizes"), sep="\t", col.names=FALSE, row.names=FALSE,quote=FALSE)
		
		# Create BS
		loginfo("prepare BS genome")
		# bismark_genome_preparation --path_to_bowtie /data/BA/public_html/HTS-flow/DB/bowtie-1.1.1/ /data/BA/public_html/HTS-flow/DB/pathFiles/genomes/test/
		bismarkCommand <- paste(getHTSFlowPath("bismark_genome_preparation"), " --path_to_bowtie ", getHTSFlowPath("bowtie_dir"), versionDir, sep=" " )
		loginfo(bismarkCommand)
		result <- system(bismarkCommand)
#		loginfo(paste0("Copy ", paste( versionDir, "Bisulfite_Genome", sep="/"), " to ", versionBsDir))		
#		file.rename(from = paste( versionDir, "Bisulfite_Genome", sep="/"), to = paste(versionBsDir, "Bisulfite_Genome", sep="/"))
		
	#}
			
	loginfo(paste0("version: ", version, " is ready"))
}

apply(genomes, 1, downloadGenome)

sessionInfo()
