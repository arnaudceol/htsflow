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
?>
<script>
 function load<?php echo $tableDiv; ?>() {              		
     $.post("pages/tables/secondary.php", {    	
        selectable: <?php echo $selectable; ?>,
        secondaryId: $('#<?php echo $tableDiv; ?>Filter').find('#secondaryId').val(),
        primaryId: $('#<?php echo $tableDiv; ?>Filter').find('#primaryId').val(),
        sampleId: $('#<?php echo $tableDiv; ?>Filter').find('#sampleId').val(),
    	description: $('#<?php echo $tableDiv; ?>Filter').find('#description').val(),
    	title: $('#<?php echo $tableDiv; ?>Filter').find('#title').val(),
    	method: $('#<?php echo $tableDiv; ?>Filter').find('#method').find(":selected").val(),
    	user_id: $('#<?php echo $tableDiv; ?>Filter').find('#user_id').find(":selected").val(),
    	status: $('#<?php echo $tableDiv; ?>Filter').find('#status').find(":selected").val(),
    	selectedIds: $('#selectedIds').val() ,
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}   
</script>

<form id="<?php echo $tableDiv; ?>Filter" name="CONTROL">
	<!--	    // FILTERING OPTIONS-->
	<fieldset class="filtertable">
		<legend>Filter table</legend>
<div class="group"><label>Secondary id</label><br/><input class="<?php echo $tableDiv; ?>SubmitKey"
						type="text" id="secondaryId" name="secondaryId" size="8"
						value="<?php if (isset( $_POST ["secondaryId"])) { echo  $_POST["secondaryId"]; } ?>" /></div>
<div class="group"><label>Primary id</label><br/><input class="<?php echo $tableDiv; ?>SubmitKey"
						type="text" id="primaryId" name="primaryId" size="8"
						value="<?php if (isset( $_REQUEST ["primaryId"])) { echo  $_REQUEST["primaryId"]; } ?>" /></div>
<div class="group"><label>Sample id</label><br/><input class="<?php echo $tableDiv; ?>SubmitKey"
						type="text" id="sampleId" name="sampleId" size="8"
						value="<?php if (isset( $_REQUEST ["sampleId"])) { echo  $_REQUEST["sampleId"]; } ?>" /></div>
<div class="group"><label>Title</label><br/><input class="<?php echo $tableDiv; ?>SubmitKey"
						type="text" id="title" name="title" size="8"
						value="<?php if (isset( $_POST ["title"])) { echo  $_POST["title"]; } ?>" /></div>
<div class="group"><label>Description</label><br/><input class="<?php echo $tableDiv; ?>SubmitKey"
						type="text" id="description" name="description" size="15"
						value="<?php if (isset( $_POST ["description"])) { echo  $_POST["description"]; } ?>" /></div>
						
<?php
$sql = "SELECT DISTINCT method FROM secondary_analysis ORDER BY method ASC;";

$result = mysqli_query($con, $sql);
?><div class="group"><label>Analysis</label><br /> <select id="method" name="method">
							<option value="" selected>All analyses</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["method"]; ?>"
								<?php if (isset ( $_POST ['method'] ) && $_POST ['method'] == $row["method"]) { echo "selected"; }?>><?php echo $row["method"]; ?></option><?php
    }
    ?>
</select></div>
<?php
    $sql = "SELECT DISTINCT users.user_id, user_name FROM primary_analysis, users WHERE users.user_id = primary_analysis.user_id ORDER BY user_name ASC;";
    $result = mysqli_query($con, $sql);
    ?>
 <div class="group"><label>User</label><br /> <select id="user_id" name="user_id">
							<option value="" selected>All user</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["user_id"]; ?>"
								<?php if (isset ( $_POST ['user_id'] ) && $_POST ['user_id'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
    }
    ?>
                        </select></div>
<div class="group"><label>Status</label><br /><select id="status" name="status">
							<option value="" selected>All status</option>
							<option value="completed"
								<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "completed") { echo "selected"; }?>>completed</option>
							<option value="running"
								<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "running") { echo "selected"; }?>>running</option>
							<option value="error"
								<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "error") { echo "selected"; }?>>error</option>
							<option value="deleted"
								<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "error") { echo "selected"; }?>>deleted</option>
					</select></div>

					<input type="button" value="FILTER"
						onclick="load<?php echo $tableDiv; ?>()" />
	</fieldset>
</form>



<?php include 'submitOnEnter.php'; ?>



	<script>
 function loadMy<?php echo $tableDiv; ?>() {                  		
     $.post("pages/tables/secondary.php", {    	
        selectable: <?php echo $selectable; ?>,        
    	user_id: "<?php echo $_SESSION["hf_user_id"]; ?>",    	
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}

 function loadMyCompleted<?php echo $tableDiv; ?>() {                  		
     $.post("pages/tables/secondary.php", {    	
        selectable: <?php echo $selectable; ?>,        
    	user_id: "<?php echo $_SESSION["hf_user_id"]; ?>", 
    	status: "completed",   	
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}

 function loadMyRunning<?php echo $tableDiv; ?>() {                  		
     $.post("pages/tables/secondary.php", {    	
        selectable: <?php echo $selectable; ?>,        
    	user_id: "<?php echo $_SESSION["hf_user_id"]; ?>",    	 
    	status: "running",
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}
 </script>

	<div style="float: left;">
		<fieldset class="filtertable">
			<legend>My analyses shortcuts</legend>
			<input type="submit" value="All"
				onclick="loadMy<?php echo $tableDiv; ?>()" /> <input type="submit"
				value="Completed"
				onclick="loadMyCompleted<?php echo $tableDiv; ?>()" /> <input
				type="submit" value="Running"
				onclick="loadMyRunning<?php echo $tableDiv; ?>()" /> <input
				type="submit" value="Download (csv format)"
				onclick="window.location='download.php'" />
		</fieldset>
	</div>
	