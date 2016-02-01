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
ini_set('display_errors', 'On');
error_reporting(E_ALL ^ E_WARNING);
require_once ("config.php");
require ('pages/dbaccess.php');

$require_permission= "primary";
include 'pages/check_login.php';

header('Content-type: text/html; charset=utf-8');

// Any selection done?
$selectedSamples = array();

if (isset($_POST['selectedIds'])) {
    $values = explode(" ", $_POST['selectedIds']);
    foreach ($values as $selectedId) {
        $value = preg_replace("/[\'\ ]/", "", $selectedId);
        if ($value != "") {
            array_push($selectedSamples, $value);
        }
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php include ("pages/header.php"); //header of the page ?>
    
    <body>
	<div id="wrapper">
        <?php
        include ("pages/menu.php"); // import of menu
        ?><div id="content"><?php
          include 'pages/primary/browse.php';
      ?>        
     </div>
     </div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>


