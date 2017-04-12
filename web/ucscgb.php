<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
session_start ();

require_once ("config.php");
require ('pages/dbaccess.php');

//header ( "Content-type: text/txt" );


$pageURL = 'http';
if (isset ( $_SERVER ["HTTPS"] ) && $_SERVER ["HTTPS"] == "on") {
	$pageURL .= "s";
}
$pageURL .= "://";
if ($_SERVER ["SERVER_PORT"] != "80") {
	$pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"];
} else {
	$pageURL .= $_SERVER ["SERVER_NAME"];
}

$tracks = array ();

if ($_GET ['type'] == 'primary' ) {
	$analysisType = 'primary';
	$analysisId =  $_GET ['id'] ;
	$queryGenome = sprintf ( "SELECT sample.sample_name, ref_genome, stranded FROM sample, pa_options, primary_analysis WHERE pa_options.id = options_id AND sample_id = sample.id AND primary_analysis.id = '%s'", $analysisId );
	
	$file= "/primary/tracks/bw/" . $analysisId . ".bw";
	
	$resGenome = mysqli_query ( $con, $queryGenome );
	
	$row = mysqli_fetch_assoc ( $resGenome );
	$htsFlowGenome = $row ["ref_genome"];
	$stranded = $row ["stranded"];	
	
	$shortLabel = $row ["sample_name"];
	
	$tracks[] = "track type=bigWig name=\"".$analysisId."_".$shortLabel."\" description=\"".$analysisId."_".$shortLabel."\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;		
		
	if ($stranded == 1) {
		$file = "/primary/tracks/bw/" . $analysisId . "_p.bw";
		$tracks[] = "track type=bigWig name=\"".$analysisId."_".$shortLabel." (+)\" description=\"".$analysisId."_".$shortLabel." (+)\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;		
		$file = "/primary/tracks/bw/" . $analysisId . "_n.bw";
		$tracks[] = "track type=bigWig name=\"".$analysisId."_".$shortLabel." (-)\" description=\"".$analysisId."_".$shortLabel." (-)\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;		
	}
	
	mysqli_free_result ( $resGenome );
} else {
	preg_match ( '/([0-9]+)\/([a-z\_]+)\.igb/', $_GET ['id'], $m );
	$analysisId =  $_GET ['id'] ;
	$analysisType =  $_GET ['type'];
	
	if ($analysisType == "peak_calling") {
		$queryGenome = sprintf ( "SELECT ref_genome, label, $analysisType.id FROM sample, primary_analysis, $analysisType WHERE sample_id = sample.id  AND primary_id = primary_analysis.id AND secondary_id = %s", $analysisId );
	} else {
		$queryGenome = sprintf ( "SELECT ref_genome, label, $analysisType.id FROM sample, primary_analysis, $analysisType WHERE sample_id = sample.id  AND primary_id = primary_analysis.id AND secondary_id = %s", $analysisId );
	}
	
	$igbQuery = mysqli_query ( $con, $queryGenome );
	while ( $igbResult = mysqli_fetch_assoc ( $igbQuery ) ) {
		$htsFlowGenome = $igbResult ["ref_genome"];
		
		$name = $_GET ['id'] . "-" . $igbResult["label"] . "-" . $igbResult["id"];
		
		if ($analysisType == "peak_calling") {
			/* For peak calling */ 
			
			$program =  $igbResult ["program"];
			if ($program == "MACSnarrow") {
				$file = "/secondary/" . $analysisId . "/bed/" . $igbResult ["label"] . "_peaks.bb";
				$tracks[] = "track type=bigBed name=\"$name\" description=\"$name\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;
			} elseif ($program == "MACSbroad") {
				$file= "/secondary/" . $analysisId . "/bed/" . $igbResult ["label"] . "_broad_peaks.bb";
				$tracks[] = "track type=bigBed name=\"$name\" description=\"$name\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;				
			} else {
				 /* both */
				$file = "/secondary/" . $analysisId . "/bed/" . $igbResult ["label"] . "_peaks.bb";
				$tracks[] = "track type=bigBed name=\"$name-narrow\" description=\"$name\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;				
				$file = "/secondary/" . $analysisId . "/bed/" . $igbResult ["label"] . "_broad_peaks.bb";
				$tracks[] = "track type=bigBed name=\"$name-broad\" description=\"$name\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;
				$file = "/secondary/" . $analysisId . "/bed/" . $igbResult ["label"] . "_both_peaks.bb";
				$tracks[] = "track type=bigBed name=\"$name-both\" description=\"$name\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;				
			} 
		} else {
				$file= "/secondary/" . $analysisId . "/bed/" . $igbResult ["label"] . "_peaks.bb";
				$tracks[] = "track type=bigBed name=\"$name\" description=\"$name\" bigDataUrl=" . $pageURL . "/" . $HTSFLOW_PATHS ["HTSFLOW_WEB_TRACKS"] .$file;				
		}			
		
	}
	mysqli_free_result ( $igbQuery );
}


foreach ( $tracks as $track ) {
	echo $track. "\n";
}
?>
