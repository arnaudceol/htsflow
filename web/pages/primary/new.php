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
?><div style="display: inline-block;" >
<?php
$tableDiv = "sampleTable";
$form_name = "RUNTABLE";
$selectable = true;
$editable = false;

include 'pages/filters/sample_filter.php';
?>
</div>
<input type="submit" style="float: right; margin: 15px; text-align: center"	
	onclick="loadSubmitTable();" value="Settings"/>
<div style="clear: both;"></div>
<script>
	function loadSubmitTable() {  
		if ($('#selectedIds').val().trim() === "") {
			alert("No row selected");
		} else {
    	$.post("pages/tables/samples.php", {
		    type: "completed",
            selectedIds: $('#selectedIds').val(),
			selectable: false,
			editable: false,
            <?php
			foreach ( $_POST as $key => $value ) {
				if ($key != "selectable") {
					echo "$key: \"$value\",\n";
				}
			}
			?>	
			}, function(response) {
    			$( "#tableSubmit" ).html(response);
    			toggle('submit_form');
		});        

    	// load settings
        $.post("pages/primary/setting_table.php", {
    			selectedIds: $('#selectedIds').val(),
    		}, function(response) {
    			$( "#settingsSubmit" ).html(response);
    		});        
        }   
	}
</script>

<form name="<?php echo $form_name; ?>"
	action="pages/primary/submit.php" method="post">
	<div id="sampleTable"></div>
        <?php
		//	$selectable = false == isset ( $_POST ["submit"] );
		?>  
		<script>
			$.post("pages/tables/samples.php", {
            	selectable: true,
    			editable: false,        	    
	       		<?php if (isset ( $_POST ['seq_method'] )) { echo "seq_method: \"" . $_POST ['seq_method'] ."\",\n"; } ?>
       			<?php if (isset ( $_POST ['user_id'] )) { echo "user_id: \"" . $_POST ['user_id'] ."\",\n" ; } ?>
       			<?php if (isset ( $_POST ['ref_genome'] )) { echo "ref_genome: \"" . $_POST ['ref_genome'] ."\",\n"; } ?>
       			<?php if (isset ( $_POST ['source'] )) { echo "source: \"" . $_POST ['source'] ."\",\n"; } ?>			 						
			}, function(response) {		   
			    $( "#sampleTable" ).html(response);
			}); 
        </script>

	<div id="submit_form" style="display: none" class="over-form">
		<div
			style="text-align: right; margin: 20px; font-style: italic; font-weight: bold"
			onclick="javascript:toggle('submit_form')">close</div>
		<div id="tableSubmit"></div>
		<div id="settingsSubmit"></div>	
	</div>
</form>

