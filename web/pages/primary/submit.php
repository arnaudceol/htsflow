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
require ('../../config.php');
require ('../run/functions.php');

// Should be called at the begining of each table script
require ('../dbaccess.php');

// first of all we checked if exists a set of options equal to the ones submitted
// if true we get the number otherwise we insert the new set, get the number and
// pass them for the new submission in primary_analysis.
$user_id = $_SESSION["hf_user_id"];
$user_name = $_SESSION["hf_user_name"];

$setValues = Array();
$query = 'SELECT id from pa_options where ';
foreach ($_POST as $key => $value) {
    if ($key == "remove_bad_reads") {
        $query .= "rm_bad_reads=" . $value . " ";
        array_push($setValues, $value);
    } elseif ($key == "trimming") {
        $query .= "and trimming=" . $value . " ";
        array_push($setValues, $value);
    } elseif ($key == "masking") {
        $query .= "and masking=" . $value . " ";
        array_push($setValues, $value);
    } elseif ($key == "aln") {
        $query .= "and alignment=" . $value . " ";
        array_push($setValues, $value);
    } elseif ($key == "aln_prog") {
        $query .= "and aln_prog='" . $value . "' ";
        array_push($setValues, '"' . $value . '"');
    } elseif ($key == "aln_options") {
        $query .= "and aln_options='" . $value . "' ";
        array_push($setValues, '"' . $value . '"');
    } elseif ($key == "paired") {
        $query .= "and paired=" . $value . " ";
        array_push($setValues, $value);
    } elseif ($key == "removeTmpfqfiles") {
        $query .= "and rm_tmp_files=" . $value . " ";
        array_push($setValues, $value);
    } elseif ($key == "removeDuplicates") {
        $query .= "and rm_duplicates=" . $value . " ";
        array_push($setValues, $value);
    }
}

$valuesToBeInserted = '';
foreach ($setValues as $val) {
    $valuesToBeInserted .= $val . ",";
}
$valuesToBeInserted = substr($valuesToBeInserted, 0, - 1);
$query .= ";";

$res = mysqli_query($con, $query);
$val_option = 0;
while ($line = mysqli_fetch_assoc($res)) {
    $val_option = $line["id"];
}

// if val_option is equal to zero we need to insert the new set options otherwise
// we have to get the id of the already present set of options and assign it
// to the new submission of samples.
if ($val_option == 0) {
    
    $totQuery = '';
    $subquery = "INSERT INTO pa_options (rm_bad_reads,trimming,masking,alignment,aln_prog,aln_options,paired,rm_tmp_files,rm_duplicates) VALUES (";
    $totQuery .= $subquery;
    $totQuery .= $valuesToBeInserted;
    $totQuery .= ");";
    $stmt = mysqli_prepare($con, $totQuery);
    
    if ($stmt) {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_close($stmt);
    }
    $res = mysqli_query($con, $query);
    while ($line = mysqli_fetch_assoc($res)) {
        $val_option = $line["id"];
    }
} 

// if everything works else
$messageYes = '';
// in case of error
$messageNo = '';


$jobs = Array();

$selectedIds = $_POST["selectedIds"];
$values = explode(" ", trim($selectedIds));
$numIds = sizeof($values);

for ($i = 0; $i < $numIds; $i++) {
//foreach ($values as $selectedId) {
    $selectedId = $_POST['selectedId' . $i];
    $description = $_POST['description' . $i];
    $sample = preg_replace("/[\'\ ]/", "", $selectedId);
    if ($sample != "") {

        $checkQuery = "SELECT ref_genome FROM sample WHERE id= '" . $sample . "';";
        
        $res = mysqli_query($con, $checkQuery);
        while ($line = mysqli_fetch_assoc($res)) {
            $refgenome = $line["ref_genome"];
        }
        // echo $refgenome;
        $checkQuery = "SELECT id FROM primary_analysis WHERE sample_id = '" . $sample . "' and options_id = " . $val_option . ";";
        //echo $checkQuery;
        $res = mysqli_query($con, $checkQuery);
        $VALUEprimary_analysis = 0;
        while ($line = mysqli_fetch_assoc($res)) {
            $VALUEprimary_analysis = $line["id"];
        }
        // echo $VALUEprimary_analysis;
        if ($VALUEprimary_analysis == 0) {
            $primaryId = getNewId();
            
            $querySample = "INSERT INTO primary_analysis (id, sample_id, options_id, status, user_id, origin, description) SELECT " . $primaryId . ", id," . $val_option . ", 'scheduled', '" . $user_id . "', source, '" .mysqli_real_escape_string($con, $description) . "' FROM sample WHERE id = '" . $sample . "';";
            error_log($querySample);
            $stmt = mysqli_prepare($con, $querySample);
            
            if ($stmt) {
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_close($stmt);
                array_push($jobs, "primary\t". $primaryId);
                $messageYes .= "Primary analysis for sample " . $sample . " has been correctly put in the queue with ID " . $primaryId .".";
            } else {
                $messageNo .= "SAMPLE " . $sample . " has NOT been put in the queue";
            }
        } else {
            $messageNo .= "SAMPLE " . $sample . " has been already analyzed with the options provided. Check the jobs in the queue or the completed ones.";
        }
        
    }
}

// Now jobs array contains all the jobs that a users has to run.
$checkFileUser = putInUserFile($jobs);

if ($messageYes != '') {
    header("Location: ../../primary-browse.php?userId=".$user_id."&messageYes=" . $messageYes);
}  else {
    header("Location: ../../primary-browse.php?userId=".$user_id."&messageNo=" . $messageNo);    
}

//message($ISINQUEUE);

