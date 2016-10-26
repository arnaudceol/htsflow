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
require ('../../config.php');
require ('../run/functions.php');

// Should be called at the begining of each table script
require ('../dbaccess.php');
require ('../primary/options.php');

$user_id = $_SESSION ["hf_user_id"];
$user_name = $_SESSION ["hf_user_name"];
$sample_name = $_POST ["merge_name"];
$seq_method = $_POST ["seq_method"];
$rm_duplicates = $_POST ["rm_duplicates"];
$stranded = $_POST ["stranded"];

$options = array (
		"rm_bad_reads" => "0",
		"trimming" => "0",
		"masking" => "0",
		"alignment" => "0",
		"paired" => "0",
		"rm_tmp_files" => "0",
		"rm_duplicates" => $rm_duplicates,
		"stranded" => $stranded 
);

$optID = getPrimaryOptionId ( $options );

$MergeData = Array (
		"sample" => Array () 
);

foreach ( $_POST as $key => $value ) {
	if (substr ( $key, 0, 6 ) == 'sample') {
		$sample = $value;
		array_push ( $MergeData ["sample"], $sample );
	}
}

// Import attributes of the original samples
foreach ( $MergeData ["sample"] as $key => $value ) {
	$MergeSample = $value;
	$checkQuery = "SELECT ref_genome, reads_mode, reads_length FROM sample, primary_analysis WHERE sample.id = sample_id AND primary_analysis.id = '" . $MergeSample . "';";
	error_log ( $checkQuery );
	$res = mysqli_query ( $con, $checkQuery );
	while ( $line = mysqli_fetch_assoc ( $res ) ) {
		$refgenome = $line ["ref_genome"];
		$readsMode = $line ["reads_mode"];
		$readsLength = $line ["reads_length"];
	}
	mysqli_free_result ( $res );
}

// Get new ID for the sample associated to this merge
$mergeSampleId = "M" . getNewId ();

$QueryMerge = "INSERT INTO sample ( id, sample_name, seq_method, user_id, ref_genome, reads_mode, reads_length, raw_data_path, source, raw_data_path_date ) VALUES ( '" . $mergeSampleId . "',  '" . $sample_name . "', '" . $seq_method . "', '" . $user_id . "', '" . $refgenome . "', '" . $readsMode . "', " . $readsLength . ", '', 1, '" . date ( 'Y-m-d H:i:s' ) . "' );";

$stmt = mysqli_prepare ( $con, $QueryMerge );
if ($stmt) {
	mysqli_stmt_execute ( $stmt );
	mysqli_stmt_store_result ( $stmt );
	mysqli_stmt_close ( $stmt );
}

$MergeData = Array (
		"sample" => Array () 
);
foreach ( $_POST as $key => $value ) {
	error_log ( "post $key => $value" );
	if (substr ( $key, 0, 6 ) == 'sample') {
		$sample = $value;
		error_log ( "add sample $sample" );
		array_push ( $MergeData ["sample"], $sample );
	}
}

$mergePrimaryId = getNewId ();

$QueryPrimary = "INSERT INTO primary_analysis ( id, sample_id, options_id, status, user_id, origin, description ) VALUES ( " . $mergePrimaryId . ", '" . $mergeSampleId . "', " . $optID . ", 'scheduled', '" . $user_id . "', 1, '" . mysqli_real_escape_string ( $con, $_POST ['description'] ) . "'  );";

$stmt = mysqli_prepare ( $con, $QueryPrimary );
if ($stmt) {
	mysqli_stmt_execute ( $stmt );
	mysqli_stmt_store_result ( $stmt );
	mysqli_stmt_close ( $stmt );
}

foreach ( $MergeData ["sample"] as $key => $value ) {
	$MergeSample = $value;
	$queryMerge2Sample = "INSERT INTO merged_primary (result_primary_id, source_primary_id) VALUES ('" . $mergePrimaryId . "','" . $MergeSample . "');";
	$stmtMerge2Sample = mysqli_prepare ( $con, $queryMerge2Sample );
	if ($stmtMerge2Sample) {
		mysqli_stmt_execute ( $stmtMerge2Sample );
		mysqli_stmt_store_result ( $stmtMerge2Sample );
		mysqli_stmt_close ( $stmtMerge2Sample );
	}
}

$jobs = Array ();
array_push ( $jobs, "merging\t" . $mergePrimaryId );
$checkFileUser = putInUserFile ( $jobs );

$messageYes = "Merging correctly inserted with the following ID: " . $mergePrimaryId . " (a sample has also been created with ID: <b>" . $mergeSampleId . "</b>)";

header ( "Location: ../../primary-browse.php?userId=" . $user_id . "&messageYes=" . $messageYes );

