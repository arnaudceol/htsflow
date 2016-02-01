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
 ?>
<div style="display: inline-block;">
<?php
$tableDiv = "tablePrimary";
$selectable = true;

include 'pages/filters/primary_table_filter.php';
include 'pages/run/functions.php';
include 'pages/primary/editFunctions.php';
?>
</div>
<div style="float: right; margin: 4px;" class="filtertable"
	onclick="loadMergeTable();">Merge selected samples</div>
<div style="float: right; margin: 4px;" class="filtertable"
	onclick="window.location.href='primary-new.php'">New primary analyses</div>
<div style="clear: both;"></div>
<div id="messages"><?php 
if (isset($_REQUEST['messageYes'])) {
    ?><div class="message"><i class="fa fa-thumbs-o-up" style="color:green"></i><?php echo $_REQUEST['messageYes']; ?></div><?php 
} if (isset($_REQUEST['messageNo'])) {
    ?><div class="message"><i class="fa fa-thumbs-o-down" style="color:red"></i><?php echo $_REQUEST['messageNo']; ?></div><?php 
}
?></div>

<form name="merging" action="" method="post">
	<div id="tablePrimary"></div>
	<script>
					$.post("pages/tables/primary.php", {
            			selectable: "true",
            			<?php
            
if (isset($_REQUEST['sampleId'])) {
                ?>            		sampleId: "<?php echo $_REQUEST['sampleId']; ?>"<?php
            }
            if (isset($_REQUEST['primaryId'])) {
                ?>            		primaryId: "<?php echo $_REQUEST['primaryId']; ?>"<?php
                        }
            ?>
					}, function(response) {
					    // 	Log the response to the console
	          		    //console.log("Response: "+response);
			    		$( "#tablePrimary" ).html(response);
					});           
	</script>

	<script>
			function loadMergeTable() {                   
				if ($('#selectedIds').val().trim().split(" ").length < 2) {
					alert("Select at least two samples.");
				} else { 		
                       $.post("pages/tables/primary.php", {
		            			selectable: "false", 
		            			type: "completed",
            					selectedIds: $('#selectedIds').val(),
            			<?php
            foreach ($_POST as $key => $value) {
                if ($key != "selectable") {
                    echo "$key: \"$value\",\n";
                }
            }
            ?>	
								}, function(response) {
					    			$( "#tableMerge" ).html(response);
					    			javascript:toggle('merging_form')
								});        
                        	 }  
			 

		    	// load settings
		        $.post("pages/merging/submit_form.php", {
		    			selectedIds: $('#selectedIds').val(),
		    		}, function(response) {
		    			$( "#settingsSubmit" ).html(response);
		    		});        
		         
			}

	</script>

	<div id="merging_form" style="display: none" class="over-form">
		<div
			style="text-align: right; margin: 20px; font-style: italic; font-weight: bold"
			onclick="javascript:toggle('merging_form')">close</div>
		<div id="tableMerge"></div>
		<div id="settingsSubmit"></div>
	</div>
</form>
