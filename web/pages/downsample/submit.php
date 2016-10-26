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

$user_id = $_SESSION ["hf_user_id"];
$user_name = $_SESSION ["hf_user_name"];
$sample_name = $_POST ["downsample_name"];
$primaryId = $_POST ["downsample_primaryId"];
$description = $_POST ["downsample_description"];
$targetNumReads = $_POST ["downsample_target_num_reads"];

global $con;
$query = "SELECT options_id FROM primary_analysis WHERE id = " . $primaryId . " ;";
error_log ( $query );
$res = mysqli_query ( $con, $query );
$line = mysqli_fetch_assoc ( $res );
if (is_null ( $line )) {
	$optID = 0;
} else {
	$optID = intval ( $line ["options_id"] );
}
mysqli_free_result ( $res );
error_log ( "OPT: " +  $optID);

$checkQuery = "SELECT ref_genome, reads_mode, reads_length, seq_method FROM sample, primary_analysis WHERE sample.id = sample_id AND primary_analysis.id = '" . $primaryId . "';";
error_log ( $checkQuery );
$res = mysqli_query ( $con, $checkQuery );
while ( $line = mysqli_fetch_assoc ( $res ) ) {
	$refgenome = $line ["ref_genome"];
	$readsMode = $line ["reads_mode"];
	$readsLength = $line ["reads_length"];
	$seq_method = $line ["seq_method"];
}
mysqli_free_result ( $res );

// Get new ID for the sample associated to this merge
$mergeSampleId = "D" . getNewId ();

$QueryMerge = "INSERT INTO sample ( id, sample_name, seq_method, user_id, ref_genome, reads_mode, reads_length, raw_data_path, source, raw_data_path_date ) VALUES ( '" . $mergeSampleId . "',  '" . $sample_name . "', '" . $seq_method . "', '" . $user_id . "', '" . $refgenome . "', '" . $readsMode . "', " . $readsLength . ", '', 3, '" . date ( 'Y-m-d H:i:s' ) . "' );";
error_log( $QueryMerge );
$stmt = mysqli_prepare ( $con, $QueryMerge );
if ($stmt) {
	mysqli_stmt_execute ( $stmt );
	mysqli_stmt_store_result ( $stmt );
	mysqli_stmt_close ( $stmt );
}

$MergeData = Array (
		"sample" => Array () 
);

$downsamplePrimaryId = getNewId ();

$QueryPrimary = "INSERT INTO primary_analysis ( id, sample_id, options_id, status, user_id, origin, description ) VALUES ( " . $downsamplePrimaryId . ", '" . $mergeSampleId . "', " . $optID . ", 'scheduled', '" . $user_id . "', 3, '" . mysqli_real_escape_string ( $con, $description) . "'  );";
error_log($QueryPrimary);
$stmt = mysqli_prepare ( $con, $QueryPrimary );
if ($stmt) {
	mysqli_stmt_execute ( $stmt );
	mysqli_stmt_store_result ( $stmt );
	mysqli_stmt_close ( $stmt );
}

$queryMerge2Sample = "INSERT INTO downsample_primary (result_primary_id, source_primary_id, target_reads_number) VALUES ('" . $downsamplePrimaryId . "','" . $primaryId . "', $targetNumReads);";
error_log($queryMerge2Sample);

$stmtMerge2Sample = mysqli_prepare ( $con, $queryMerge2Sample );
if ($stmtMerge2Sample) {
	mysqli_stmt_execute ( $stmtMerge2Sample );
	mysqli_stmt_store_result ( $stmtMerge2Sample );
	mysqli_stmt_close ( $stmtMerge2Sample );
}

$jobs = Array ();
array_push ( $jobs, "downsampling\t" . $downsamplePrimaryId );
$checkFileUser = putInUserFile ( $jobs );

$messageYes = "Downsampling correctly inserted with the following ID: " . $downsamplePrimaryId . " (a sample has also been created with ID: <b>" . $mergeSampleId . "</b>)";

header ( "Location: ../../primary-browse.php?userId=" . $user_id . "&messageYes=" . $messageYes );

