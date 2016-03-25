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

secondaryPipeline <- function( IDsec ){
	loginfo (paste("START Secondary Analysis:" , IDsec))
	
	SQLmethod <- paste( "SELECT method FROM secondary_analysis WHERE id=",IDsec,";")
	tmp <- dbQuery(SQLmethod)
	method <- tmp[2]
	
	if ( method == "expression_quantification" ) {
		expressionQuantification( IDsec )
	} else if ( method == "differential_gene_expression") {
		differentialGeneExpression( IDsec )		
	} else if ( method == "peak_calling") {
		peakCalling( IDsec )		
	} else if ( method == "footprint_analysis") {
		footprintAnalysis( IDsec )
	} else if ( method == 'methylation_calling') {
		methylationCalling( IDsec )
	} else if ( method == 'inspect') {
		inspect( IDsec )
	}	
}



