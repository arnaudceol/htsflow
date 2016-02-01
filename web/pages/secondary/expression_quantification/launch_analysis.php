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

$ExprData = Array(
    "sample" => Array(),
    "mix" => Array()
);
foreach ($_POST as $key => $value) {
    if (substr($key, 0, 6) == 'sample') {
        $sample = $value;
        array_push($ExprData["sample"], $sample);
    }
    if (substr($key, 0, 3) == 'mix') {
        $mix = $value;
        array_push($ExprData["mix"], $mix);
    }
}
// Array ( [sample] => Array ( ) [mix] => Array ( [0] => 1 [1] => 1 ) )
for ($i = 0; $i < count($ExprData["sample"]); $i ++) {
    $queryExpr = "INSERT INTO expression_quantification (secondary_id, primary_id,mix_spike) VALUES ('" . $new_id_sec . "','" . $ExprData["sample"][$i] . "','" . $ExprData["mix"][$i] . "');";
    $stmtExpr = mysqli_prepare($con, $queryExpr);
    if ($stmtExpr) {
        mysqli_stmt_execute($stmtExpr);
        mysqli_stmt_store_result($stmtExpr);
        mysqli_stmt_close($stmtExpr);
    } else {
        ?>Some jobs have not been put in queue, please contact the administrator.
<br /><?php
    }
}
