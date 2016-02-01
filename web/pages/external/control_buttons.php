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
// THIS FUNCTION CONTROLS THE BUTTONS FOR STOP AND RESTART OF A JOB
// CHANGE AT YOUR OWN RISK.
global $con;
$user_id = $_SESSION["hf_user_id"];

if (isset($_POST['submitExt'])) {
    /* print "SUBMITTING!!!"; */
    
    $seq_method = $_POST['seq_method'];
    $reads_length = $_POST['reads_length'];
    $reads_mode = $_POST['reads_mode'];
    $ref_genome = $_POST['ref_genome'];
    
    $paths = $_POST['paths'];
    echo "<div style=\"clear:both; height:10px;\"></div>";
    echo "<div style='background-color:#ffffff; padding: 50px;'>\n";
    $Value = array();
    if (is_null($seq_method)) {
        $mex = "You have to specify a Sequencing method.";
        echo $mex . "<br />";
    } else {
        array_push($Value, $seq_method);
    }
    array_push($Value, $reads_length);
    if (is_null($reads_mode)) {
        $mex = "You have to specify Reads Type.";
        echo $mex . "<br />";
    } else {
        array_push($Value, $reads_mode);
    }
    if (is_null($ref_genome)) {
        $mex = "You have to specify Reference Genome.";
        echo $mex . "<br />";
    } else {
        array_push($Value, $ref_genome);
    }

    if ($paths == "") {
        $mex = "You have to specify at least one path.";
        echo $mex . "<br />";
    } else {
        array_push($Value, $paths);
    }

    if (count($Value) == 5) {
        $finalPath = Array();
        
        foreach (explode("\n", $Value[4]) as $path) {
            $path = trim($path);
            if (trim($path) != "") {
                if (substr($path, - 1) != "/") {
                    $path = $path . "/";
                }
                array_push($finalPath, $path);
            }
        }
        
        // now we check the existance of each path.
        for ($i = 0; $i < count($finalPath); $i ++) {
            if (file_exists(trim($finalPath[$i]))) {
                // first let's check that this sample has not yet been inserted.
                $externalSampleId = getNewId();
                // get sample name from directory
                $output = explode("/", $finalPath[$i]);
                $sampleName = $output[count($output) - 2];

                $Query = "INSERT INTO sample (id, sample_name, seq_method, reads_length, reads_mode, ref_genome, raw_data_path, user_id, source, raw_data_path_date) SELECT 'X" . $externalSampleId . "', '" . $sampleName . "','" . $seq_method . "','" . $reads_length . "','" . $reads_mode . "','" . $ref_genome . "','" . $finalPath[$i] . "', user_id, 2, '".date('Y-m-d H:i:s')."' FROM users WHERE user_name = '" . $_SESSION["hf_user_name"] . "';";

                $stmt = mysqli_prepare($con, $Query);
                if ($stmt) {
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_close($stmt);
                    echo "<span>Sample: " . $sampleName . " has been put in the database with ID X" . $externalSampleId . ".</span><br />\n";
                }
            } else {
                echo $finalPath[$i] . " does not exists or is not accessible.<br />";
            }
        }
    }
    echo "</div>";
}