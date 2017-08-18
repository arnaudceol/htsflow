<?php

// Get or create options
function getPrimaryOptionId($options) {
	global $con;

	$setAttributes = Array();
	$setValues = Array();
	$setQueryAttribute = Array();
	
	foreach ( $options as $key => $value ) {
		if ($key == "remove_bad_reads") {
// 			$query .= "rm_bad_reads=" . $value . " ";
			array_push ( $setQueryAttribute,  "rm_bad_reads=" . $value );
			array_push ( $setAttributes, "rm_bad_reads" );
			array_push ( $setValues, $value );
		} elseif ($key == "trimming") {
// 			$query .= "and trimming=" . $value . " ";
			array_push ( $setQueryAttribute, "trimming=" . $value );
			array_push ( $setAttributes, "trimming" );
			array_push ( $setValues, $value );
		} elseif ($key == "masking") {
// 			$query .= "and masking=" . $value . " ";
			array_push ( $setQueryAttribute, "masking=" . $value );
			array_push ( $setAttributes, "masking" );
			array_push ( $setValues, $value );
		} elseif ($key == "aln") {
// 			$query .= "and alignment=" . $value . " ";
			array_push ( $setQueryAttribute,  "alignment=" . $value );
			array_push ( $setAttributes, "alignment" );
			array_push ( $setValues, $value );
		} elseif ($key == "aln_prog") {
// 			$query .= "and aln_prog='" . $value . "' ";
			array_push ( $setQueryAttribute,  "aln_prog='" . $value . "' ");
			array_push ( $setAttributes, "aln_prog" );
			array_push ( $setValues, '"' . $value . '"' );
		} elseif ($key == "aln_options") {
// 			$query .= "and aln_options='" . $value . "' ";
			array_push ( $setQueryAttribute,  "aln_options='" . $value . "' ");
			array_push ( $setAttributes, "aln_options" );
			array_push ( $setValues, '"' . $value . '"' );
		} elseif ($key == "paired") {
// 			$query .= "and paired=" . $value . " ";
			array_push ( $setQueryAttribute,  "paired=" . $value);
			array_push ( $setAttributes, "paired" );
			array_push ( $setValues, $value );
		} elseif ($key == "rm_tmp_files") {
// 			$query .= "and rm_tmp_files=" . $value . " ";
			array_push ( $setQueryAttribute,  "rm_tmp_files=" . $value );
			array_push ( $setAttributes, "rm_tmp_files" );
			array_push ( $setValues, $value );
		} elseif ($key == "rm_duplicates") {
// 			$query .= "and rm_duplicates=" . $value . " ";
			array_push ( $setQueryAttribute, "rm_duplicates=" . $value );
			array_push ( $setAttributes, "rm_duplicates" );
			array_push ( $setValues, $value );
		} elseif ($key == "stranded") {
// 			$query .= "and stranded=" . $value . " ";
			array_push ( $setQueryAttribute, "stranded=" . $value );
			array_push ( $setAttributes, "stranded" );
			array_push ( $setValues, $value );
		} elseif ($key == "genome") {
			// 			$query .= "and stranded=" . $value . " ";
			array_push ( $setQueryAttribute, "genome='" . $value . "'" );
			array_push ( $setAttributes, "genome" );
			array_push ( $setValues, "'" . $value . "'" );
		}
	}
	
	// Complete missing options:
	
	if (! in_array("rm_bad_reads", $setAttributes)) {
		$value = 0;
		array_push ( $setQueryAttribute,  "rm_bad_reads=" . $value );
		array_push ( $setAttributes, "rm_bad_reads" );
		array_push ( $setValues, $value );
	} 
	
	if (! in_array("trimming", $setAttributes) ) {
		$value = 0;
		array_push ( $setQueryAttribute, "trimming=" . $value );
		array_push ( $setAttributes, "trimming" );
		array_push ( $setValues, $value );
	}
	
	if (! in_array("masking", $setAttributes)  ) {
		$value = 0;
		array_push ( $setQueryAttribute, "masking=" . $value );
		array_push ( $setAttributes, "masking" );
		array_push ( $setValues, $value );
	} 
	
	if (! in_array("alignment", $setAttributes) ) {
		$value = 1;
		array_push ( $setQueryAttribute,  "alignment=" . $value );
		array_push ( $setAttributes, "alignment" );
		array_push ( $setValues, $value );
	} 
	
	if (! in_array("aln_prog", $setAttributes)  ) {
		array_push ( $setQueryAttribute,  "aln_prog='' ");
		array_push ( $setAttributes, "aln_prog" );
		array_push ( $setValues, '""' );
	} 
	
	if (! in_array("aln_options", $setAttributes)  ) {		
		array_push ( $setQueryAttribute,  "aln_options='' ");
		array_push ( $setAttributes, "aln_options" );
		array_push ( $setValues, '""' );
	} 
	
	if (! in_array("genome", $setAttributes)  ) {
		array_push ( $setQueryAttribute,  "genome='' ");
		array_push ( $setAttributes, "genome" );
		array_push ( $setValues, '""' );
	} 
	
	if (! in_array("paired", $setAttributes)  ) {
		$value = 0;
		array_push ( $setQueryAttribute,  "paired=" . $value);
		array_push ( $setAttributes, "paired" );
		array_push ( $setValues, $value );
	} 
	
	if (! in_array("rm_tmp_files", $setAttributes)  ) {
		$value = 1;
		array_push ( $setQueryAttribute,  "rm_tmp_files=" . $value );
		array_push ( $setAttributes, "rm_tmp_files" );
		array_push ( $setValues, $value );
	} 
	
	if (! in_array("rm_duplicates", $setAttributes)  ) {
		$value = 0;
		array_push ( $setQueryAttribute, "rm_duplicates=" . $value );
		array_push ( $setAttributes, "rm_duplicates" );
		array_push ( $setValues, $value );
	} 
	
	if (! in_array("stranded", $setAttributes)  ) {
		$value = 0;
		array_push ( $setQueryAttribute, "stranded=" . $value );
		array_push ( $setAttributes, "stranded" );
		array_push ( $setValues, $value );
	}
	
	
	
	
	
	
	// $valuesToBeInserted = '';
	// foreach ($setValues as $val) {
	// $valuesToBeInserted .= $val . ",";
	// }
	// $valuesToBeInserted = substr($valuesToBeInserted, 0, - 1);
	$query = 'SELECT id from pa_options where ' .  implode ( " AND ", $setQueryAttribute ) ;
	error_log($query);
	$res = mysqli_query ( $con, $query );
	
	while ( $line = mysqli_fetch_assoc ( $res ) ) {
		return $line ["id"];
	}
	
	// We need to insert the new set options otherwise
	// we have to get the id of the already present set of options and assign it
	// to the new submission of samples.
	
	// $totQuery = ''; //rm_bad_reads,trimming,masking,alignment,aln_prog,aln_options,paired,rm_tmp_files,rm_duplicates, stranded
	$totQuery = "INSERT INTO pa_options (" . implode ( ",", $setAttributes ) . ") VALUES (";
// 	$totQuery .= $subquery;
	$totQuery .= implode ( ",", $setValues );
	$totQuery .= ");";
	error_log($totQuery);
	$stmt = mysqli_prepare ( $con, $totQuery );
	
	if ($stmt) {
		mysqli_stmt_execute ( $stmt );
		mysqli_stmt_store_result ( $stmt );
		mysqli_stmt_close ( $stmt );
	}
	
	error_log ( $query );
	$res = mysqli_query ( $con, $query );
	while ( $line = mysqli_fetch_assoc ( $res ) ) {
		return $line ["id"];
	}
}


