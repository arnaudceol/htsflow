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

differentialGeneExpression <- function( IDsec ){
	method = "differential_gene_expression"
	
	loginfo ("Start differentially Expressed Genes Analysis")
	
	values <- getSecondaryData(IDsec, method)
	
	if ( checkMixPresence(values) ) {
		loginfo ("Normalization with mix")
	} else {
		
		library( DESeq2 , quietly = TRUE)
		library( RColorBrewer , quietly = TRUE)
		library( gplots , quietly = TRUE)
		
		# next line will check the reference genome to which each sample has been aligned.
		# if there are more then one reference genome, it raises an error, otherwise it will
		# continue with the right annotation.
		REFGENOME <- unique( unlist( sapply( values$primary_id, function(x) extractInfoFromDB( paste0('SELECT genome FROM pa_options, primary_analysis WHERE pa_options.id = options_id AND primary_analysis.id=',x ) ) ) ) )
		if ( length(REFGENOME) > 1 ) {
			setSecondaryStatus( IDsec=IDsec, status='genome error..', endTime=T )
			logerror("REFGENOME error. The user provided samples with more than one reference genome.")
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
			COUNTfold <- paste ( getHTSFlowPath("HTSFLOW_COUNT"), "/", sep="" )
			sampleFiles <- sapply(1:length(values$primary_id), function(x) paste(COUNTfold, values$primary_id[x], ".count", sep="") )
			# sampleName <- values$S1
			sampleNamesSQL <- paste0(
					"SELECT sample_name FROM sample, differential_gene_expression, primary_analysis WHERE sample_id = sample.id AND source = origin  AND primary_id = primary_analysis.id  AND secondary_id="
					,IDsec
					,";"
			)
			sampleName <- extractSingleColumnFromDB(sampleNamesSQL)
			conditions <- values$cond
			exp_name <- values$title[1]
			exp_name <- as.character(exp_name)
			replicates <- table(conditions)
			# loading count files
			# update the DB with the secondary analysis status DESeq2 analysis
			setSecondaryStatus( IDsec=IDsec, status='DESeq2 analysis..', startTime=T )
			
				
			numGenes <- getNumberOfGenes(REFGENOME)
			matCounts <- sapply(1:length(sampleFiles), function( x ) {
						tmp <- read.table(sampleFiles[x], header=T, nrows=numGenes, stringsAsFactors=FALSE, row.names=1)
						reads <-  tmp[,length(tmp)]
						names(reads) <- rownames(tmp)
						reads
					} )
			colnames(matCounts) <- unlist(sampleName)
			colData_dds <- data.frame(treatment=conditions)
			rownames(colData_dds) <- unlist(sampleName)
			TvsCdds <- DESeqDataSetFromMatrix( countData = matCounts, colData = colData_dds, design = ~treatment )
			
			# creating output folder
			outFolder <- paste ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/", sep="" )
			loginfo( paste("Create output folder ",outFolder," --",sep="") )
			createDir(outFolder,  recursive =  TRUE)	
			
			# DISTANCE HEATMAP
			rld <- rlog(TvsCdds)
			distsRL <- dist(t(assay(rld)))
			mat <- as.matrix(distsRL)
			hmcol <- colorRampPalette(brewer.pal(9, "GnBu"))(100)
			hc <- hclust(distsRL)
			pdf(paste0(outFolder,'distance.pdf'))
			heatmap.2(
					mat
					,Rowv=as.dendrogram(hc)
					,symm=TRUE
					,trace="none"
					,col = rev(hmcol)
					,margin=c(15, 15)
			)
			dev.off()
			
			# DEG analysis
			TvsCdds <- DESeq(TvsCdds)
			TvsC <- results( TvsCdds )
			TvsC <- TvsC[names(EG2GSlist[rownames(TvsC)][!sapply(EG2GSlist[rownames(TvsC)], is.null)]),]
			rownames(TvsC) <- EG2GSlist[rownames(TvsC)]
			TvsC <- TvsC[order(rownames(TvsC)),]
			
			DEGs <- data.frame( TvsC[1], TvsC[2], TvsC[3], TvsC[4], TvsC[5], TvsC[6] )
			
			loginfo(head(DEGs))
			
			setwd( outFolder )
			
			# update the DB with the secondary analysis status complete
			setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
			
			saveRDS( DEGs, paste0( exp_name,".rds" ) )
			saveRDS( TvsCdds, paste0( exp_name,"_dds.rds" ) )
		}
	}
}