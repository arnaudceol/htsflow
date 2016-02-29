<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

$require_permission = "secondary";
include 'pages/check_login.php';

include 'pages/secondary/common/editFunctions.php';
include 'pages/run/functions.php';

// Any selection done?
$selectedSamples = array();

foreach ($_POST as $key => $value) {
    // this is not the good solution, the field shoud have a different name
    if (preg_match("/^selected_/", $key)) {
        array_push($selectedSamples, $value);
    }
}

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php include ("pages/header.php"); //header of the page ?>
    <body>
	<div id="wrapper">
<?php
include ("pages/menu.php"); // import of menu
?><div id="content">
<div style="display: inline-block;">
<?php

$tableDiv = "tableSecondary";
$selectable = "true";

include 'pages/filters/secondary_filter.php';

?>
</div>
	
	
	<script>
	function goToSample() {                   
		if ($('#selectedIds').val().trim() == '') {
			alert("Select at least one samples.");
		} else {	
			$('body').append($('<form/>')
					  .attr({'action': 'samples.php', 'method': 'post', 'id': 'toPrimaryForm'})
					  .append($('<input/>')
					    .attr({'type': 'hidden', 'name': 'secondaryId', 'value': $('#selectedIds').val().replace( /\'/g, '')  })
					  )
					).find('#toPrimaryForm').submit();
		 }  
	}

	function goToPrimary() {                   
		if ($('#selectedIds').val().trim() == '') {
			alert("Select at least one sample.");
		} else {	
			$('body').append($('<form/>')
					  .attr({'action': 'primary-browse.php', 'method': 'post', 'id': 'toPrimaryForm'})
					  .append($('<input/>')
					    .attr({'type': 'hidden', 'name': 'secondaryId', 'value': $('#selectedIds').val().replace( /\'/g, '')  })
					  )
					).find('#toPrimaryForm').submit();
		 }  
	}

</script>
	
	
	
			<div style="float: right;">
				<fieldset class="filtertable">
					<legend>Actions</legend>
					<a href="#" class="fa fa-plus-square fa-2x"
						onclick="window.location.href='secondary-new.php'"
						title="New secondary analyses"></a>
						<a href="#"
							class="fa fa-reply-all fa-2x" onclick="goToSample();return false;"
							title="Show samples for selected analyses"></a> 
					<a href="#"   class="fa fa-reply fa-2x" 
				onclick="goToPrimary();return false;" title="Show primary analysis for selected secondary" ></a>
			
				</fieldset>
			</div>
			<?php 

if (isset($_REQUEST['messageYes'])) {
    ?><div class="message">
				<i class="fa fa-thumbs-o-up" style="color: green"></i><?php echo $_REQUEST['messageYes']; ?></div><?php
}
?>
    
    <?php
    
if (isset($_REQUEST['messageNo'])) {
        ?><div class="message">
				<i class="fa fa-thumbs-o-down" style="color: red"></i><?php echo $_REQUEST['messageNo']; ?></div><?php
    }
    ?>
<div style="clear: both;"></div>
			<div id="tableSecondary"></div>
			<script>
					$.post("pages/tables/secondary.php", {
            			selectable: true,	
            			<?php   if (isset($_REQUEST['primaryId'])) {
            			?>primaryId: "<?php echo $_REQUEST['primaryId']; ?>",<?php }
            			if (isset($_REQUEST['sampleId'])) {
            			?> sampleId: "<?php echo $_REQUEST['sampleId']; ?>",
            			<?php    }            			 
                        if (isset($_REQUEST['secondaryId'])) {
                        ?> secondaryId: "<?php echo $_REQUEST['secondaryId']; ?>",<?php }  ?>
					}, function(response) {
					    // 	Log the response to the console
	          		    //console.log("Response: "+response);
			    		$( "#tableSecondary" ).html(response);
					});           
	</script>

		</div>

	</div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>
