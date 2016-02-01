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
error_reporting(E_ALL);

// I don't know if you need to wrap the 1 inside of double quotes.
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);


require_once ("config.php");
require ('pages/dbaccess.php');

$require_permission= "browse";
include 'pages/check_login.php';

// Externalize this
require_once ("pages/run/functions.php");

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php include ("pages/header.php"); //header of the page ?>
    <body>
	<div id="wrapper">
        <?php
        include ("pages/menu.php"); // import of menu
        ?><div id="content"><?php
          
            global $con;
            $user_id = $_SESSION["hf_user_id"] ;
            
            include 'pages/tables/users.php';
            
            ?><div style="padding: 30px"></div>
            
            <a class="filtertable" style="text-decoration: none" href="download.php">Download</a> information about all your secondary analysis and related samples (csv format).
			<div style="clear: both;"></div>
            
            <div style="padding: 30px"></div>
            
            <?php
            Checker();
            
            global $con;
            $user_id = $_SESSION["hf_user_id"] ;
            include 'pages/filters/analysis_filter.php';
            ?>
				
					<div id="tableAnalysis"></div>
					<script>			
					$.post("pages/tables/analyses.php", {						
            			selectable: "false",  
               			<?php if (isset ( $_POST ['method'] )) { echo "method: \"" . $_POST ['method'] ."\",\n"; } ?>
               			<?php if (isset ( $_POST ['user_id'] )) { echo "user_id: \"" . $_POST ['user_id'] ."\",\n" ; } ?>
               			<?php if (isset ( $_POST ['level'] )) { echo "level: \"" . $_POST ['level'] ."\",\n"; } ?>	   		
               			<?php if (isset ( $_POST ['status'] )) { echo "status: \"" . $_POST ['status'] ."\",\n"; } ?>	   							
					}, function(response) {
					    // Log the response to the console
	          		    //  console.log("Response: "+response);
					    $( "#tableAnalysis" ).html(response);
					});           
            </script>
				</div>
     </div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>


<?php

function Checker()
{
    global $con;
    
    if (count($_POST) != 0) {
        foreach ($_POST as $key => $value) {
            if ($key == 'analysis' and $value == 'primary') {
                include 'pages/run/check_primary_analysis.php';
            } else 
                if ($key == 'analysis' and $value == 'secondary') {
                    include 'pages/run/check_secondary_analysis.php';
                } else 
                    if ($key == 'analysis' and $value == 'merging') {
                        include 'pages/run/check_merging_analysis.php';
                    }
        }
    }
}


