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

include 'pages/run/functions.php';
include 'pages/primary/editFunctions.php';
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
					    .attr({'type': 'hidden', 'name': 'primaryId', 'value': $('#selectedIds').val().replace( /\'/g, '')  })
					  )
					).find('#toPrimaryForm').submit();
		 }  
	}

	function goToSecondary() {     
		if ($('#selectedIds').val().trim() == '') {
			alert("Select at least one analysis.");
		} else {	
			$('body').append($('<form/>')
					  .attr({'action': 'secondary-browse.php', 'method': 'post', 'id': 'toSecondaryForm'})
					  .append($('<input/>')
					    .attr({'type': 'hidden', 'name': 'primaryId', 'value': $('#selectedIds').val().replace( /\'/g, '')  })
					  )
					).find('#toSecondaryForm').submit();
		 }  
	}


	</script>
	<div style="display: inline-block;">
<?php 
include 'pages/filters/primary_table_filter.php';
?>
</div>
<div style="float: right;">
	<fieldset class="filtertable">
		<legend>Actions</legend>
		<a href="#" class="fa fa-plus-square fa-2x"
			onclick="window.location.href='primary-new.php'"
			title="New primary analyses"></a> 
		<a href="#"
			class="fa fa-object-group  fa-2x"
			onclick="loadMergeTable(); return false;"
			title="Merge selected samples"></a>
		<a href="#"
			class="fa fa-filter  fa-2x"
			onclick="loadDownsampleTable(); return false;"
			title="Downsample"></a>
		<a href="#"
			class="fa fa-reply fa-2x" onclick="goToSample();return false;"
			title="Show samples for selected analyses"></a> 
		<a href="#"
			class="fa fa-share fa-2x" onclick="goToSecondary();return false;"
			title="Show secondary analysis for selected primary analyses"></a>
	</fieldset>
</div>


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

           if (isset($_REQUEST['secondaryId'])) {
                ?>            		secondaryId: "<?php echo $_REQUEST['secondaryId']; ?>"<?php
            }
            ?>
					}, function(response) {
					    // 	Log the response to the console
	          		    //console.log("Response: "+response);
			    		$( "#tablePrimary" ).html(response);
					});           
	</script>

	<script>

	function loadDownsampleTable() {                   

		if ($('#selectedIds').val().trim().split(" ") == "" || $('#selectedIds').val().trim().split(" ").length > 2 ) {
			alert("Select at least one sample, and at most two sample (the number of reads in the smallest sample with be usedas a base for the down sampling).");
		} else { 		
               $.post("pages/tables/primary.php", {
            			selectable: "false", 
            			type: "completed",
            		    browsable: "false",
            			limit: "all",     	
    					selectedIds: $('#selectedIds').val(),
    			<?php
    foreach ($_POST as $key => $value) {
        if ($key != "selectable") {
            echo "$key: \"$value\",\n";
        }
    }
    ?>	
						}, function(response) {
			    			$( "#tableDownsample" ).html(response);
			    			javascript:toggle('downsample_form')
						});        
                	 }  
	 

    	// load settings
        $.post("pages/downsample/submit_form.php", {
    			selectedIds: $('#selectedIds').val(),
    		}, function(response) {
    			$( "#settingsSubmitDownSample" ).html(response);
    		});        
         
	};
	
			function loadMergeTable() {                   
				if ($('#selectedIds').val().trim().split(" ").length < 2) {
					alert("Select at least two samples.");
				} else { 		
                       $.post("pages/tables/primary.php", {
		            			selectable: "false", 
		            			type: "completed",
		            		    browsable: "false",
		            			limit: "all",     	
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
	<div id="downsample_form" style="display: none" class="over-form">
		<div
			style="text-align: right; margin: 20px; font-style: italic; font-weight: bold"
			onclick="javascript:toggle('downsample_form')">close</div>
		<div id="tableDownsample"></div>
		<div id="settingsSubmitDownSample"></div>
	</div>
</form>
