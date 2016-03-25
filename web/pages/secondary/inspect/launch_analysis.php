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


$deg_during_pulse = $_POST["deg_during_pulse"];
$modeling_rates = $_POST["modeling_rates"];
$counts_filtering = $_POST["counts_filtering"];
$inspect_type = $_POST["inspect_type"];

$DEGData = Array(
    "foursu_primary_id" => Array(),
    "rnatotal_primary_id" => Array(),
    "condition" => Array(),
    "timepoint" => Array()
);

foreach ($_POST as $key => $value) {
    if (substr($key, 0, 17) == 'foursu_primary_id') {
        $sample = $value;
        array_push($DEGData["foursu_primary_id"], $sample);
    }
    
    if (substr($key, 0, 19) == 'rnatotal_primary_id') {
        $sample = $value;
        array_push($DEGData["rnatotal_primary_id"], $sample);
    }
    
    if (substr($key, 0, 9) == 'condition') {
        $condition = $value;
        array_push($DEGData["condition"], $condition);
    }
    if (substr($key, 0, 9) == 'timepoint') {
        $mix = $value;
        array_push($DEGData["timepoint"], $mix);
    }
}

error_log("num elements: " . count($DEGData["foursu_primary_id"]));
for ($i = 0; $i < count($DEGData["foursu_primary_id"]); $i ++) {
    $queryDEG = "INSERT INTO inspect (secondary_id,primary_id,rnatotal_id,cond,timepoint,deg_during_pulse,modeling_rates,counts_filtering,type ) VALUES ('" . $new_id_sec . "','" . $DEGData["foursu_primary_id"][$i] . "','" 
    				. $DEGData["rnatotal_primary_id"][$i] . "','" . $DEGData["condition"][$i] . "'," . $DEGData["timepoint"][$i]. ", " .$deg_during_pulse . "," .$modeling_rates. "," . $counts_filtering. ",'" . $inspect_type ."');";
   error_log($queryDEG);
    $stmtDEG = mysqli_prepare($con, $queryDEG);
    if ($stmtDEG) {
        mysqli_stmt_execute($stmtDEG);
        mysqli_stmt_store_result($stmtDEG);
        mysqli_stmt_close($stmtDEG);
    } else {
        ?>Some jobs have not been put in queue, please contact the administrator.
<br /><?php
    }
}
                  
