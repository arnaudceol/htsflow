## library('GEOmetadb', quietly = TRUE)
## library('SRAdb', quietly = TRUE)

#Function to connect to SRA database
#Returns a connection to perform queries
connectToSRADB <- function(){
	sqliteFileName <- paste0(getHTSFlowPath('HTSFLOW_GEODB'), "/SRAmetadb.sqlite")
	#creation of the connection to the database
	sra_con <- dbConnect(SQLite(), sqliteFileName)
	return(sra_con)
}

#Function to connect to GEOmetadb database
#Returns a connection to perform queries
connectToGEOmetaDB <- function(){
	sqliteFileName <- paste0(getHTSFlowPath('HTSFLOW_GEODB'), "/GEOmetadb.sqlite")
	
	#creation of the connection to the database
	geo_con <- dbConnect(SQLite(), sqliteFileName)
	return(geo_con)
}


downloadSRA <- function(sra_con, destinationDir, SRX_id){
	getSRAfile(SRX_id, sra_con, destDir = destinationDir, fileType = 'sra', 
			srcType = 'fasp', makeDirectory = FALSE, method = 'curl', ascpCMD = '/hpcnfs/home/ieo3004/.aspera/cli/bin/ascp -QT -l 50m  -v -i /hpcnfs/home/ieo3004//.aspera/cli/etc/asperaweb_id_dsa.openssh ' )
}

