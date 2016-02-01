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
$DEGData = Array(
    "sample" => Array(),
    "condition" => Array(),
    "mix" => Array()
);
foreach ($_POST as $key => $value) {
    if (substr($key, 0, 6) == 'sample') {
        $sample = $value;
        array_push($DEGData["sample"], $sample);
    }
    if (substr($key, 0, 9) == 'condition') {
        $condition = $value;
        array_push($DEGData["condition"], $condition);
    }
    if (substr($key, 0, 3) == 'mix') {
        $mix = $value;
        array_push($DEGData["mix"], $mix);
    }
}

for ($i = 0; $i < count($DEGData["sample"]); $i ++) {
    $queryDEG = "INSERT INTO differential_gene_expression (secondary_id,primary_id,cond,mix_spike) VALUES ('" . $new_id_sec . "','" . $DEGData["sample"][$i] . "','" . $DEGData["condition"][$i] . "','" . $DEGData["mix"][$i] . "');";
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
                  