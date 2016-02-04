<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
session_start();

require_once ("config.php");
require ('pages/dbaccess.php');

header("Content-type: text/txt");
$primaryId = str_replace('/primary.igb', '', $_GET['id']);

// get info from primary 
$queryGenome = sprintf("SELECT ref_genome FROM sample, primary_analysis WHERE sample_id = sample.id AND primary_analysis.id = '%s'", $primaryId);
$resGenome = mysqli_query($con, $queryGenome);
$htsFlowGenome =  mysqli_fetch_assoc($resGenome)["ref_genome"];

// Read synonyms:
$synonymsUrl = "https://bitbucket.org/lorainelab/integrated-genome-browser/raw/1194f9df27bb771cac7422af28c673a46a4d22b4/core/synonym-lookup/src/main/resources/synonyms.txt";
$fp = fopen($synonymsUrl, 'r');



$synonymToGenome = array();

$file = file_get_contents($synonymsUrl);
while ( !feof($fp) )
{
    $line = fgets($fp, 2048);
     $delimiter = "\t";
     $data = str_getcsv($line, $delimiter);
 	$genome = $data[0];
     foreach ( $data as $synonym ) {
     	$synonymToGenome[$synonym] = $genome;   	
     }
 }                              

 fclose($fp);
 $igbGenome = $synonymToGenome[$htsFlowGenome];
//$igbGenome =$htsFlowGenome;
//   http://127.0.0.1:7085/UnibrowControl?scriptfile=http://wiki.transvar.org/confluence/download/attachments/17269487/add_new_data.igb
// http://127.0.0.1:7085/UnibrowControl?scriptfile=http://localhost:3030/htsflow2/web/igb.php?id=12980
?>
# Scripting and the IGB command language
# 
# How to add a new data set

# Primary analyses: <?php echo $primaryId; ?>, genome: <?php echo $htsFlowGenome; ?>

# select genome of choice
genome <?php echo $igbGenome . "\n"; ?>
# Add a new data set
#load <?php //echo $HTSFLOW_PATHS["HTSFLOW_WEB_OUTPUT"] ?>/primary/tracks/bw/<?php echo $primaryId; ?>.bw
load http://www.bioinfo.ieo.eu/BAgroup/htsflow2-data/primary/tracks/bw/<?php echo $primaryId; ?>.bw
# load the data
refresh



