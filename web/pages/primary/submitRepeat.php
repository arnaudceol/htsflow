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

// Should be called at the begining of each table script
require ('../dbaccess.php');

$id = $_POST["ID"];
$subquery = "UPDATE primary_analysis SET status='waiting to (re)start' WHERE id=" . $id . ";";
$stmt = mysqli_prepare($con, $subquery);
if ($stmt) {
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_close($stmt);
}
$user_id = $_SESSION["hf_user_id"];
$sqlQuery = "insert into job_list (analyses_type , analyses_id , action , user_id ) values ('primary', " . $id . ", 'run', " . $user_id . ") ";
$res = mysqli_query($con, $sqlQuery);

$message = "Primary analysis " . $id . " will restart.";

header("Location: ../../primary-browse.php?userId=" . $user_id . "&messageYes=" . $message);
