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

footprintAnalysis <- function( IDsec ){
	method = "footprint_analysis"

	loginfo ("Footprint analysis")
	
	values <- getSecondaryData(IDsec, method)
	
	SQLstr <- paste0(
			'SELECT p.program, p.primary_id, p.secondary_id,
					p.input_id, p.label, f.peak_id, f.caller, f.pvalue
					FROM secondary_analysis s, footprint_analysis f, peak_calling p
					WHERE s.id=f.secondary_id and p.id=f.peak_id and s.id = '
			,IDsec
	)
	flags <- extractInfoFromDB(SQLstr)
	
	
	library(compEpiTools, quietly = TRUE)
	# update the DB with the secondary analysis status
	SQL <- paste0("UPDATE secondary_analysis SET status='preparing data..', dateStart=NOW() WHERE id=",IDsec)
	res <- updateInfoOnDB( SQL )

	# creating output secondary folder
	outFolder <- paste ( getHTSFlowPath("HTSFLOW_SECONDARY"), IDsec,"/", sep="" )
	loginfo( paste("Create output folder ",outFolder," --",sep="") )
	
	createDir(outFolder,  recursive =  TRUE)	
	
	# creating folder for footprints results
	footprintFolder <- paste0(outFolder, 'footprints/')
	createDir(footprintFolder,  recursive =  TRUE)	
	
	bamFiles <- sapply( flags$primary_id, function(x) paste0(getHTSFlowPath("HTSFLOW_ALN"), '/', x, '.bam') )
	names(bamFiles) <- NULL
	bedFiles <- sapply( 1:dim(flags)[1], function(x)
				if ( flags[x,]$program == 'MACSnarrow' ) {
					paste0( getHTSFlowPath("HTSFLOW_SECONDARY"), flags[x,]$secondary_id, "/NARROW/", flags[x,]$label, '_peaks.bed' )
				} else {
					paste0(  getHTSFlowPath("HTSFLOW_SECONDARY"), flags[x,]$secondary_id, "/BROAD/", flags[x,]$label, '_broad_peaks.bed' )
				}
	)
	genome <- unique( unlist( sapply( flags$imput_id, function(x) extractSingleColumnFromDB(paste0('SELECT genome FROM pa_options, primary_analysis WHERE pa_options.id = options_id AND primary_analysis.id = ',x)) ) ) )
	genomePaths <- getGenomePaths( genome )
	
	for( i in 1:length(bedFiles)){
		BED <- bedFiles[i]
		FOOT <- paste0( footprintFolder, flags[i,]$label, "_footprints.txt")
		BBfoot <- paste0( footprintFolder, flags[i,]$label, "_footprints.bb")
		FOOTRDS <- paste0( footprintFolder, flags[i,]$label, "_footprints.rds")
		BAM <- bamFiles[i]
		PVALUE <- log10( as.numeric( flags[i,]$pvalue ) )
		SQL <- paste0( 'SELECT paired from primary_analysis, pa_options WHERE pa_options.id = options_id AND primary_analysis.id=', flags[i,]$primary_id )
		
		if ( as.numeric( extractInfoFromDB(SQL)$paired ) == 0 ) {
			TYPEOFANALYSIS <- 'singleEnd'
		} else {
			TYPEOFANALYSIS <- 'pairEnd'
		}
		# now we have to modify the bed file for wellington algorithm
		# we have to substitute the \t with spaces and retaining only the
		# first three columns
		loginfo( 'Creating temporary files for footprint analysis' )
		BEDtmp <- system('mktemp', intern=T)
		BEDtmp2 <- system('mktemp', intern=T)
		cmd <-  paste0( "cat ", BED," | sed 's/\t/ /g' > ", BEDtmp )
		tryOrExit(cmd,  "prepare BED")
		
		cmd <- paste0( "cat ", BEDtmp," | cut -d ' ' -f 1,2,3 > ", BEDtmp2 )
		tryOrExit(cmd,  "prepare BED")
		
		deleteFile(BEDtmp)
		
		
		# 4 pvalue, 5 singleEnd
		# Then we have to remove not aligned reads from BAM file
		# update the DB with the secondary analysis status
		SQL <-
				paste(
						"UPDATE secondary_analysis SET status='removing not aligned reads..' WHERE id="
						,IDsec
						,sep=""
				)
		res <- updateInfoOnDB( SQL )
		BAMtmp <- tempfile()
		cmd <- paste0('touch ', BAMtmp)
		tryOrExit(cmd,  "prepare BAM")
		
		cmd <- paste0(  getHTSFlowPath("samtools"), ' view -h -F 4 ', BAM, ' | ', getHTSFlowPath("samtools"), ' view -bS - > ', BAMtmp )
		tryOrExit(cmd,  "samtool, view")
		
		BAItmp <- tempfile()
		system( paste0('touch ', BAItmp) )
		cmd <- paste0(  getHTSFlowPath("samtools"), ' index ', BAMtmp )
		tryOrExit(cmd,  "samtool, index")
		
		SQL <-
				paste(
						"UPDATE secondary_analysis SET status='running "
						,unique( flags$caller )
						," on "
						,flags[i,]$label
						,"..' WHERE id="
						,IDsec
						,sep=""
				)
		res <- updateInfoOnDB( SQL )
		cmd <-  paste0( getHTSFlowPath("python"), " ", getHTSFlowPath("wellington"), " ", BEDtmp2, " ", BAMtmp, " ", FOOT, " ", PVALUE, " ", TYPEOFANALYSIS )
		tryOrExit(cmd,  "Wellington")
		
		tryCatch(
				GR <- createGR( FOOT ),						
				error = function(e)
				{	
					setError("Cannot create GR from footprint output")
					loginfo(e)
				}
		)	
				
		saveRDS( GR, file = FOOTRDS )	
		
		#### remove bed temporary files
		loginfo ( 'removing temporary files' )
		cmd <- paste0( 'rm ',BEDtmp2, " ", BAMtmp)
		loginfo(cmd)
		tryOrExit(cmd,  "remove temporary files")
		
	}

	# update the DB with the secondary analysis status complete
	setSecondaryStatus( IDsec=IDsec, status='completed', endTime=T, outFolder=T )
}


createGR <- function( FILE ){
	TempGR <- read.table( FILE, sep="\t", stringsAsFactors=F)
	GR <- GRanges(Rle(as.character(TempGR$V1)),IRanges(TempGR$V2, TempGR$V3), Rle(TempGR$V6))
	name <- TempGR$V4
	pvalue <- as.numeric(TempGR$V5)
	GR$name <- name
	GR$pvalue <- pvalue
	return(GR)
}


