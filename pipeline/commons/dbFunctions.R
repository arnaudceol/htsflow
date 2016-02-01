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

library('raster', quietly = TRUE)


dbQuery <- function(sql) {
	dbConfig <- readIniFile(getHTSFlowPath("DB_CONF"), aslist = TRUE)	
	user <- dbConfig$database$username
	host <- dbConfig$database$hostname
	pass <- dbConfig$database$pass
	database <- dbConfig$database$dbname
	baseSQL <- paste0( 'mysql',' -h ',host,' -u ',user,' -p',pass,' ',database,' -e ')
	composeSQL <- paste0( baseSQL, "\"", sql, "\"")
	
	tryCatch(
		result <- system(composeSQL, intern=T),
		error = function(e)
		{
			logerror("SQL query failed: %",  sql)
			return(-1); #print(e$message) # or whatever error handling code you want
		}
	)
	
	return (result)
}


extractInfoFromDB <- function( SQL ) {
	tmp <- dbQuery(SQL)
	header <- strsplit( tmp[1], split="\t" )[[1]]
	elements <- sapply( tmp[2:length(tmp)], function(x) strsplit(x, split="\t") )
	sample_names <- unlist ( sapply( names(elements), function(x) strsplit(x, split="\t")[[1]][1] ) )
	names(sample_names) <- NULL
	names(elements) <- sample_names
	val <- as.data.frame( t ( sapply( elements, function(x) rbind( x ) ) ), stringsAsFactors=F )
	val <- droplevels(val)
	rownames(val) <- NULL
	colnames(val) <- header
	return (val)
}

updateInfoOnDB <- function( SQL ) {
	tmp <- dbQuery(SQL)
	return(tmp)
}

extractSingleColumnFromDB <- function( SQL ) {
	tmp <- dbQuery(SQL)
	header <- strsplit( tmp[1], split="\t" )[[1]]
	elements <- sapply( tmp[2:length(tmp)], function(x) strsplit(x, split="\t") )
	val <- as.data.frame( sapply( elements, function(x) rbind( x ) ), stringsAsFactors=F )
	val <- droplevels(val)
	rownames(val) <- NULL
	colnames(val) <- header
	return (val)
}


setError <- function( failedCommand ) {
	if (exists("PIPELINE_ID", envir=globalenv())) {
		# type is primary or secondary
		id <- get("PIPELINE_ID", envir=globalenv())
		type <- get("PIPELINE_TYPE", envir=globalenv())
		if (type == 'merge') {
			# primary and merge are stored in the same table
			type = 'primary'
		}
		logerror(failedCommand)
		SQL <- paste0( 'UPDATE ', type , '_analysis SET status=\'Error: ', gsub("'","''",failedCommand),'\', dateEnd=NOW() WHERE id = ', id )	
		tmp <- dbQuery(SQL)
	} else {
		loginfo("PIPELINE_ID not set. This should not cause the error by itself but it means that the error will not be reported in the database")
		logerror(failedCommand)
	}
}



# For each merging, one sample AND one primary analyses are added
# Here we need the id of the primary
getFlagsElementsFromMergeDB <- function( id_merge_primary ) {
	SQL <- paste0(
			"SELECT primary_analysis.id as id_merge, sample_name, seq_method, reads_num, project, primary_analysis.user_id FROM sample, primary_analysis WHERE sample.id = sample_id AND source = 1 AND primary_analysis.id = "
			,id_merge_primary
			,";"
	)
	tmp <- dbQuery(SQL)
	header <- strsplit( tmp[1], split="\t" )[[1]]
	elements <- sapply( tmp[2:length(tmp)], function(x) strsplit(x, split="\t") )
	sample_names <- unlist ( sapply( names(elements), function(x) strsplit(x, split="\t")[[1]][1] ) )
	names(sample_names) <- NULL
	names(elements) <- sample_names
	values <- as.data.frame( t ( sapply( elements, function(x) rbind( x ) ) ), stringsAsFactors=F  )
	values <- droplevels(values)
	rownames(values) <- NULL
	colnames(values) <- header
	return (values)
}

getSecondaryData <- function(IDsec, method) {
	
	SQLstr <- paste("SELECT ",method,".*, title  from  ",method,", secondary_analysis WHERE secondary_id = secondary_analysis.id AND secondary_id =",IDsec,";")
	tmp <- dbQuery(SQLstr)
	
	header <- strsplit( tmp[1], split="\t" )[[1]]
	elements <- sapply( tmp[2:length(tmp)], function(x) strsplit(x, split="\t") )
	sample_names <- unlist ( sapply( names(elements), function(x) strsplit(x, split="\t")[[1]][1] ) )
	names(sample_names) <- NULL
	names(elements) <- sample_names
	values <- as.data.frame( t ( sapply( elements, function(x) rbind( x ) ) ), stringsAsFactors=F  )
	values <- droplevels(values)
	
	
	if ( method != 'peak_calling' || method != 'footprint_analysis' || method != 'methylation_calling' ) {
		# to check if spikes in are included
		if ( length(header) != dim(values)[2] ) {
			#this means that there are no spikes-in selected
			spikes <- rep( NA, dim(values)[1] )
			values <- cbind( values, spikes )
		}
	}
	rownames(values) <- NULL
	colnames(values) <- header
	
	return (values)
}


setSecondaryStatus <- function( IDsec, status='completed', startTime=FALSE, endTime=FALSE, outFolder=FALSE ) {
	
	SQL <- paste0( 'UPDATE secondary_analysis SET status=\'', status,'\' ' )
	if ( startTime ) {
		SQL <- paste0( SQL, ', dateStart=NOW()' )
	}
	if ( endTime ) {
		SQL <- paste0( SQL, ', dateEnd=NOW()' )
	}
	if ( outFolder ) {
		SQL <- paste0( SQL, ', foldOut=\'', getHTSFlowPath("HTSFLOW_SECONDARY"),'/', IDsec, '/\'' )
	}
	SQL <- paste0( SQL, ' WHERE id =', IDsec )
	tmp <- dbQuery(SQL)
}

setStatus <- function( id, type, status='completed', startTime=FALSE, endTime=FALSE, outFolder=FALSE ) {
	
	SQL <- paste0( 'UPDATE ', type, '_analysis SET status=\'', status,'\' ' )
	if ( startTime ) {
		SQL <- paste0( SQL, ', dateStart=NOW()' )
	}
	if ( endTime ) {
		SQL <- paste0( SQL, ', dateEnd=NOW()' )
	}
	if ( outFolder ) {
		SQL <- paste0( SQL, ', foldOut=\'', getHTSFlowPath("HTSFLOW_SECONDARY"),'/', id, '/\'' )
	}
	SQL <- paste0( SQL, ' WHERE id =', id )
	tmp <- dbQuery(SQL)
}


