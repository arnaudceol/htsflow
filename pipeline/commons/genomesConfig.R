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

# Import information about available species, assemblies and associated libraries



initSpecies <- function() {
	genomeListFile <- getHTSFlowPath("HTSFLOW_GENOMES_CONF")
	loginfo(paste0("Read genomes configuration from ", genomeListFile ))
	genomes <- read.table(genomeListFile ,header=TRUE, sep=" ")
	apply(genomes, 1, downloadGenome)
}


downloadGenome <- function(genome) {
	species <- genome[1]
	host <- genome[2]
	version <- genome[3]
	txdbLib <- genome[4]	
	annotationLibName <- genome[5]
	tableName <- genome[6]
	taxid <- genome[7]
	
	
	if (!exists("htsflowGenomeVersions")) {
		htsflowGenomeVersions <<- c();
		htsflowGenomeVersionsToTxdbLib <<- c();
		htsflowGenomeVersionsToAnnotationLib <<- c();
		htsflowSpeciesToVersions <<- c();		
	}
	
	htsflowGenomeVersions <<- c(htsflowGenomeVersions, version);
	htsflowGenomeVersionsToTxdbLib[[version]] <<- txdbLib;
	htsflowGenomeVersionsToAnnotationLib[[version]] <<- annotationLibName;
	htsflowSpeciesToVersions[[species]] <<-version;	
	loginfo(species)
}

