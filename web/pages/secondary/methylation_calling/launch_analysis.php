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

$no_overlap = $_POST["no_overlap"];
$read_context = $_POST["read_context"];
$methylationData = Array(
    "sample" => Array()
);
foreach ($_POST as $key => $value) {
    if (substr($key, 0, 6) == 'sample') {
        $sample = $value;
        if ($sample != '') {
            array_push($methylationData["sample"], $sample);
        }
    }
}
foreach ($methylationData["sample"] as $key => $value) {
    $methylationSample = $value;
    $query_methylation = "INSERT INTO methylation_calling ( secondary_id, primary_id, no_overlap, read_context ) 
    		VALUES ('" . $new_id_sec . "', " . $methylationSample . "," . $no_overlap . ",'" . $read_context . "');";
    
    $stmtMETH = mysqli_prepare($con, $query_methylation);
    if ($stmtMETH) {
        $a = mysqli_stmt_execute($stmtMETH);
        $b = mysqli_stmt_store_result($stmtMETH);
        $c = mysqli_stmt_close($stmtMETH);
    } else {
        ?>Some jobs have not been put in queue, please contact the administrator.
<br /><?php
    }
}