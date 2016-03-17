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

require_once ("config.php");

require_once ('pages/dbaccess.php');
define("DB_HOST", $hostname);
define("DB_NAME", $dbname);
define("DB_USER", $username);
define("DB_PASS", $pass);

//$require_permission= "admin";
include 'pages/check_login.php';


$type=$_GET['type'];
$id=$_GET['id'];

if ($type == "html") {
	$file = OUTPUT_FOLDER . "/QC/". id."_fastqc/fastqc_report.html";
} elseif ($type == "zip") {
	$file = OUTPUT_FOLDER . "/QC/". id."_fastqc/fastqc_report.html";
}
