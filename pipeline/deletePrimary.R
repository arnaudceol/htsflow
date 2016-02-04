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

deletePrimary <- function( ID ){
	
	setStatus( ID, 'primary' , status='deleting...',endTime=TRUE )
	
	BAM <- paste0( getHTSFlowPath("HTSFLOW_ALN") ,"/" , ID, '.bam' )
	COUNT <- paste0( getHTSFlowPath("HTSFLOW_COUNT") ,"/" , ID, '.count' )
	COUNTsummary <- paste0( getHTSFlowPath("HTSFLOW_COUNT") ,"/" , ID, '.count.summary' )
	BW <- paste0( getHTSFlowPath("HTSFLOW_PRIMARY"), '/tracks/bw/', ID, '.bw' )
	QC <- paste0( getHTSFlowPath("HTSFLOW_QC"), '/', ID )
	QCZIP <- paste0( getHTSFlowPath("HTSFLOW_QC"), '/', ID , ".zip")
	
	print( paste0( 'Removing files for ID: ', ID ) )
	
	if (file.exists(BAM)){
		loginfo(paste0("Delete BAM", BAM))
		deleteFile(BAM)
	}
	if (file.exists(COUNT)) {
		loginfo(paste0("Delete COUNT", COUNT))
		deleteFile(COUNT)
		loginfo(paste0("Delete COUNTsummary", COUNTsummary))
		deleteFile(COUNTsummary)
	}
	if (file.exists(BW)) {
		loginfo(paste0("Delete BW", BW))
		deleteFile(BW)
	}
	
	if (file.exists(QC)) {
		loginfo(paste0("Delete QC", QC))
		deleteFile(QC, recursive=TRUE)
	}
	
	
	if (file.exists(QCZIP)) {
		loginfo(paste0("Delete QC zip", QCZIP))
		deleteFile(QCZIP)
	}
	
	
	if (file.exists(getPreprocessDir())) {
		loginfo(paste0("Delete preprocess directory", getPreprocessDir()))
		deleteFile(getPreprocessDir(), recursive=TRUE)
	}
	print ( paste0( 'Updating the database..' ) )
	setStatus( ID, 'primary' , status='deleted',endTime=TRUE )
	
}
