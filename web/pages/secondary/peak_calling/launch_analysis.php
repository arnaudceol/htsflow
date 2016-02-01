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

$exp_name = $_POST["exp_name"];
$program = $_POST["program"];
$pvalue = $_POST["pvalue"];
$stats = $_POST["stats"];
if ( isset($_POST["saturation"]) && $_POST["saturation"] == 'true') {
    $saturation = 1;
} else {
    $saturation = 0;    
}

$PeakCallData = Array(
    "base" => Array(),
    "input" => Array(),
    "label" => Array()
);

foreach ($_POST as $key => $value) {
    if (substr($key, 0, 4) == 'base') {
        $base = $value;
        array_push($PeakCallData["base"], $base);
    }
    if (substr($key, 0, 5) == 'input') {
        $input = $value;
        array_push($PeakCallData["input"], $input);
    }
    if (substr($key, 0, 5) == 'label') {
        $label = $value;
        array_push($PeakCallData["label"], $label);
    }
}
for ($i = 0; $i < count($PeakCallData["base"]); $i ++) {
    $queryPeakCall = "INSERT INTO peak_calling ( secondary_id, program, primary_id, input_id, label, pvalue, stats, saturation ) VALUES ( '" . $new_id_sec . "', '" . $program . "', '" . $PeakCallData["base"][$i] . "', '" . $PeakCallData["input"][$i] . "', '" . $PeakCallData["label"][$i] . "', '" . $pvalue . "', '" . $stats . "', ". $saturation ." );";
//$queryPeakCall = "INSERT INTO peak_calling ( id_sec_fk, exp_name, program, S1, S2, label, pvalue, stats, saturation ) VALUES ( '".$new_id_sec."', '".$exp_name."', '".$program."', '".$PeakCallData["base"][$i]."', '".$PeakCallData["input"][$i]."', '".$PeakCallData["label"][$i]."', '".$pvalue."', '".$stats."', ". $saturation ." );";
    
    //echo '<br />'.$queryPeakCall.'<br />';
    $stmtPeak = mysqli_prepare($con, $queryPeakCall);
    if ($stmtPeak) {
        mysqli_stmt_execute($stmtPeak);
        mysqli_stmt_store_result($stmtPeak);
        mysqli_stmt_close($stmtPeak);
    } else {
        ?>Some jobs have not been put in queue, please contact the administrator.
<br /><?php
    }
}