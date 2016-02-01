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

if (isset ( $_POST ['submitSEQMETHOD'] )) {
	$id_sample = $_POST ["ID"];
	$subquery = "UPDATE sample SET seq_method='" . trim( mysqli_real_escape_string($con, $_POST ["TEXTdescription"] )) . "' WHERE id='" . $id_sample . "';";
	$stmt = mysqli_prepare ( $con, $subquery );
	if ($stmt) {
		mysqli_stmt_execute ( $stmt );
		mysqli_stmt_store_result ( $stmt );
		mysqli_stmt_close ( $stmt );
	}
}
if (isset ( $_POST ['submitREFGENOME'] )) {
	$id_sample = $_POST ["ID"];
	$subquery = "UPDATE sample SET ref_genome='" . trim( mysqli_real_escape_string($con, $_POST ["REFGENOMEdescription"])) . "' WHERE id='" . $id_sample . "';";
	$stmt = mysqli_prepare ( $con, $subquery );
	if ($stmt) {
		mysqli_stmt_execute ( $stmt );
		mysqli_stmt_store_result ( $stmt );
		mysqli_stmt_close ( $stmt );
	}
}
if (isset ( $_POST ['submitSAMPLENAME'] )) {
	$id_sample = $_POST ["ID"];
	$subquery = "UPDATE sample SET sample_name='" . trim( mysqli_real_escape_string($con, $_POST ["TEXTdescription"] )) . "' WHERE id='" . $id_sample . "';";
	$stmt = mysqli_prepare ( $con, $subquery );
	if ($stmt) {
		mysqli_stmt_execute ( $stmt );
		mysqli_stmt_store_result ( $stmt );
		mysqli_stmt_close ( $stmt );
	}
}

if (isset ( $_POST ['submitDescription'] )) {
    $id_sample = $_POST ["ID"];
    $description= $_POST ["description"];
    $subquery = sprintf("INSERT INTO sample_description (sample_id, description) VALUES ('%s', '%s')  ON DUPLICATE KEY UPDATE description = '%s'", $id_sample, $description, $description);
    $stmt = mysqli_prepare ( $con, $subquery );
    if ($stmt) {       
        mysqli_stmt_execute ( $stmt );
        mysqli_stmt_store_result ( $stmt );
        mysqli_stmt_close ( $stmt );
    }
}



