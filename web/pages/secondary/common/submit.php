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
require ('../../../config.php');
require ('../../run/functions.php');

// Should be called at the begining of each table script
require ('../../dbaccess.php');



function RecoverIDsec()
{
    global $con;
    $query = "SELECT id FROM secondary_analysis ORDER BY id DESC LIMIT 1;";
    $res = mysqli_query($con, $query);
    $line = mysqli_fetch_assoc($res);
    if (is_null($line)) {
        return 0;
    } else {
        return intval($line["id"]);
    }
}



$user_id = $_SESSION["hf_user_id"] ; // in seguito qui ci andrà l'utente
$user_name = $_SESSION["hf_user_name"];
?><p><?php


// if everything works else
$messageYes = '';
// in case of error
$messageNo = '';


$new_id_sec = RecoverIDsec() + 1;
$description = trim($_POST ["description"]);
$title = trim($_POST ["title"]);
foreach ($_POST as $key => $value) {
    if ($key == "method") {
        $QuerySec = "INSERT INTO secondary_analysis (id, method, user_id, status, title, description) VALUES ('" . $new_id_sec . "','" . $value . "','" . $user_id . "','queue', '" .mysqli_real_escape_string($con, $title) . "', '" .mysqli_real_escape_string($con, $description) . "');";
        $stmt = mysqli_prepare($con, $QuerySec);
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_close($stmt);
            $messageYes .= "Job correctly inserted with the following ID: ".$new_id_sec;
        }
        include '../' . $value . '/launch_analysis.php';
    }
}
?>
</p>

<?php
$jobs = Array();

array_push($jobs, "secondary\t". $new_id_sec);
$checkFileUser = putInUserFile($jobs);

if ($messageYes != '') {
    header("Location: ../../../secondary-browse.php?userId=".$user_id."&messageYes=" . $messageYes);
}  else {
    header("Location: ../../../secondary-browse.php?userId=".$user_id."&messageNo=" . $messageNo);
}

