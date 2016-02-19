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

$files = array();


if (strpos($_GET['id'], 'primary')) {
	$analysisType = 'primary';
	$analysisId = str_replace('/primary.igb', '', $_GET['id']);
	$queryGenome = sprintf("SELECT ref_genome FROM sample, primary_analysis WHERE sample_id = sample.id AND primary_analysis.id = '%s'", $analysisId);
	$files[] = "/primary/tracks/bw/" .  $analysisId . ".bw";
	$resGenome = mysqli_query($con, $queryGenome);
	$htsFlowGenome =  mysqli_fetch_assoc($resGenome)["ref_genome"];	
	mysqli_free_result($resGenome);
} else {
	preg_match('/([0-9]+)\/([a-z\_]+)\.igb/',$_GET['id'],$m);
	$analysisId = $m[1];
	$analysisType = $m[2];
	$queryGenome = sprintf("SELECT ref_genome, label FROM sample, primary_analysis, $analysisType WHERE sample_id = sample.id  AND primary_id = primary_analysis.id AND secondary_id = %s", $analysisId);

	$igbQuery = mysqli_query($con, $queryGenome);
	while($igbResult = mysqli_fetch_assoc($igbQuery)) {
		$htsFlowGenome = $igbResult["ref_genome"];	
		$files[] = "/secondary/" .  $analysisId . "/bed/" . $igbResult["label"] . "_peaks.bb";
	}
	mysqli_free_result($igbQuery);
}
// get info from primary 
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

 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
 	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
 	$pageURL .= $_SERVER["SERVER_NAME"];
 }
 
 
 ?>
# Scripting and the IGB command language
# 
# How to add a new data set

# Primary analyses: <?php echo $analysisId; ?>, genome: <?php echo $htsFlowGenome; ?>

# select genome of choice
genome <?php echo $igbGenome . "\n"; ?>
# Add a new data set
<?php  foreach ($files as $file) { 
echo "load " .$pageURL ."/" . $HTSFLOW_PATHS["HTSFLOW_WEB_OUTPUT"] . $file . "\n";
}
?>

refresh



