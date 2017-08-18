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

expressionQuantification <- function( IDsec ) {
	method = "expression_quantification"
	
	loginfo ("Start Expression Quantification Analysis")
	
	values <- getSecondaryData(IDsec, method)
	
	if ( checkMixPresence(values) ) {
		loginfo ("Normalization with mix")
	} else {		
		REFGENOME <- unique( unlist( sapply( values$primary_id, function(x) extractInfoFromDB( paste0('SELECT genome FROM pa_options, primary_analysis WHERE pa_options.id = options_id AND primary_analysis.id=',x ) ) ) ) )
		
		if ( length(REFGENOME) > 1 ) {
			setSecondaryStatus( IDsec=IDsec, status='genome error..', endTime=T )
			loginfo("REFGENOME error. The user provided samples with more than one reference genome.")
		} else {
			if ( REFGENOME == 'mm9' || REFGENOME == 'mm10' ) {
				library( org.Mm.eg.db , quietly = TRUE)
				EG2GSlist <- as.list( org.Mm.egSYMBOL )
			}
			if ( REFGENOME == 'hg18' || REFGENOME == 'hg19' ) {
				library( org.Hs.eg.db , quietly = TRUE)
				EG2GSlist <- as.list( org.Hs.egSYMBOL )
			}
			if ( REFGENOME == 'dm6' ) {
				library( org.Dm.eg.db , quietly = TRUE)
				EG2GSlist <- as.list( org.Dm.egSYMBOL )
			}
			if ( REFGENOME == 'rn5' ) {
				library( org.Rn.eg.db , quietly = TRUE)
				EG2GSlist <- as.list( org.Rn.egSYMBOL )
			}
			
			# update the DB with the secondary analysis status deconvolute expression
			setSecondaryStatus( IDsec=IDsec, status='Deconvolute expression..', startTime=T )
	
			outFolder <- paste ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/", sep="" )
			loginfo( paste("Create output folder ",outFolder," --",sep="") )
			createDir(outFolder,  recursive =  TRUE)		
			
			IDpreList <- values$primary_id
			loginfo(values)
			loginfo(IDpreList)
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
			
			if (dim(sampleNames)[1] == 1) {
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
				
				RPKMS <- as.matrix( RPKMS[names(EG2GSlist[rownames(RPKMS)][!sapply(EG2GSlist[rownames(RPKMS)], is.null)]),] )
				rownames(RPKMS) <- EG2GSlist[rownames(RPKMS)]
				RPKMS <- as.matrix( RPKMS[order(rownames(RPKMS)),] )
				
				colnames(RPKMS) <- getColumnNames(IDpreList, sampleNames)
				RPKMS <- as.data.frame(RPKMS)
				loginfo("Column names and head of RPKMS: ")
				loginfo (colnames(RPKMS))
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
				
				eRPKMS <- as.matrix( eRPKMS[names(EG2GSlist[rownames(eRPKMS)][!sapply(EG2GSlist[rownames(eRPKMS)], is.null)]),] )
				rownames(eRPKMS) <- EG2GSlist[rownames(eRPKMS)]
				eRPKMS <- as.matrix( eRPKMS[order(rownames(eRPKMS)),] )
				
				colnames(eRPKMS) <- getColumnNames(IDpreList, sampleNames)
				
				eRPKMS <- as.data.frame(eRPKMS)
				loginfo (head(eRPKMS))
				loginfo ( paste( "Saving eRPKMS.rds in ", outFolder, sep="" ) )
				saveRDS(eRPKMS, file=paste(outFolder,"eRPKMS.rds",sep=""))
				
			} else {
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
			}
			# update the DB with the secondary analysis status complete
			setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )			
		}
	}
}

# Get the sample names in the right order
getColumnNames <- function( IDpreList, sampleNames ) {
	idToName<-c()
	for ( i in 1:length(sampleNames$sample_name)) {		
		print(paste("Id: ", sampleNames$id[i]))
		idToName[sampleNames$id[i]] <-sampleNames$sample_name[i]
	}
	columnNames <- sapply(1:length(IDpreList), function(x) paste0(idToName[IDpreList[x]], ""))
	return(columnNames)
}
