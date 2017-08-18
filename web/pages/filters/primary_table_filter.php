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
// Default filter for primary analysis table.
?>
<script>
 function load<?php echo $tableDiv; ?>() {                  		
     $.post("pages/tables/primary.php", {    	
        selectable: <?php echo $selectable; ?>,
        primaryId: $('#<?php echo $tableDiv; ?>Filter').find('#primaryId').val(),
        sampleId: $('#<?php echo $tableDiv; ?>Filter').find('#sampleId').val(),
       	sampleName: $('#<?php echo $tableDiv; ?>Filter').find('#sampleName').val(),
    	description: $('#<?php echo $tableDiv; ?>Filter').find('#description').val(),
    	seqMethod: $('#<?php echo $tableDiv; ?>Filter').find('#seqMethod').find(":selected").val(),
    	status: $('#<?php echo $tableDiv; ?>Filter').find('#status').find(":selected").val(),
    	user_id: $('#<?php echo $tableDiv; ?>Filter').find('#user_id').find(":selected").val(),
    	sample_owner: $('#<?php echo $tableDiv; ?>Filter').find('#sample_owner').find(":selected").val(),
    	genome: $('#<?php echo $tableDiv; ?>Filter').find('#genome').find(":selected").val(),
    	source: $('#<?php echo $tableDiv; ?>Filter').find('#source').find(":selected").val(),
    	selectedIds: $('#selectedIds').val() ,
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}
</script>
<fieldset class="filtertable">
	<legend>Filter table</legend>
	<form id="<?php echo $tableDiv; ?>Filter" name="CONTROL">
		<div class="group">
			<label>Primary id</label><br /> <input
				class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="primaryId"
				name="primaryId" size="8"
				value="<?php if (isset( $_POST ["primaryId"])) { echo  $_POST["primaryId"]; } ?>" />
		</div>
		<div class="group">
			<label>Sample id</label><br />
			<input class="<?php echo $tableDiv; ?>SubmitKey" type="text"
				id="sampleId" name="sampleId" size="8"
				value="<?php if (isset( $_POST ["sampleId"])) { echo  $_POST["sampleId"]; } ?>" />
		</div>
		<div class="group">
			<label>Sample name</label><br />
			<input class="<?php echo $tableDiv; ?>SubmitKey" type="text"
				id="sampleName" name="sampleName" size="10"
				value="<?php if (isset( $_POST ["sampleName"])) { echo  $_POST["sampleName"]; } ?>" />
		</div>
		<div class="group">
			<label>Description</label><br />
			<input class="<?php echo $tableDiv; ?>SubmitKey" type="text"
				id="description" name="description" size="15"
				value="<?php if (isset( $_POST ["description"])) { echo  $_POST["description"]; } ?>" />
		</div>
				
<?php
$sql = "SELECT DISTINCT seq_method FROM sample, primary_analysis  WHERE sample_id = sample.id AND source <> 1 ORDER BY seq_method ASC;";

$result = mysqli_query($con, $sql);
?><div class="group">
			<label>Sequencing method</label><br /> <select id="seqMethod"
				name="seqMethod">
				<option value="" selected>All sequencing method</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["seq_method"]; ?>"
					<?php if (isset ( $_POST ['seqMethod'] ) && $_POST ['seqMethod'] == $row["seq_method"]) { echo "selected"; }?>><?php echo $row["seq_method"]; ?></option><?php
    }
    mysqli_free_result($result);
    ?>
                        </select>
		</div>

				<?php
    $sql = "SELECT DISTINCT users.user_id, user_name FROM primary_analysis, users WHERE users.user_id = primary_analysis.user_id ORDER BY user_name ASC;";
    $result = mysqli_query($con, $sql);
    ?>
<div class="group">
			<label>User</label><br /> <select id="user_id" name="user_id">
				<option value="" selected>All user</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["user_id"]; ?>"
					<?php if (isset ( $_POST ['user_id'] ) && $_POST ['user_id'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
    }
    mysqli_free_result($result);
    ?>
                        </select>
		</div>
				<?php
    $sql = "SELECT DISTINCT users.user_id, user_name FROM sample, users, primary_analysis WHERE sample_id = sample.id AND users.user_id = sample.user_id ORDER BY user_name ASC;";
    $result = mysqli_query($con, $sql);
    ?>
<div class="group">
			<label>Sample owner</label><br /> <select id="sample_owner"
				name="sample_owner">
				<option value="" selected>All sample submitters</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option
					value="<?php
        
        echo $row["user_id"];
        ?>"
					<?php if (isset ( $_POST ['sample_owner'] ) && $_POST ['sample_owner'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
    }
    mysqli_free_result($result);
    ?>
                        </select>
		</div>
				
				
				<?php
    $sql = "SELECT DISTINCT genome FROM pa_options , primary_analysis  WHERE pa_options.id = options_id AND origin <> 1 ORDER BY genome ASC;";
    $result = mysqli_query($con, $sql);
    ?>
<div class="group">
			<label>Reference genome</label><br /> <select id="genome"
				name="genome">
				<option value="" selected>All reference genomes</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["genome"]; ?>"
					<?php if (isset ( $_POST ['genome'] ) && $_POST ['genome'] == $row["genome"]) { echo "selected"; }?>><?php echo $row["genome"]; ?></option><?php
    }
    mysqli_free_result($result);
    ?>
                        </select>
		</div>
				<?php
    $sql = "SELECT DISTINCT source FROM sample ORDER BY source ASC;";
    $result = mysqli_query($con, $sql);
    ?>
<div class="group">
			<label>Source</label><br /> <select id="source" name="source">
				<option value="" selected>All sources</option>
						<?php
    $mergeOptions = array(
        0 => "<span>lims</span>",
        1 => "<span>merged</span>",
        2 => "<span>external</span>",
        3 => "<span>downsampling</span>"
    );
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["source"]; ?>"
					<?php if (isset ( $_POST ['source'] ) && $_POST ['source'] == $row["source"]) { echo "selected"; }?>><?php echo $mergeOptions[$row["source"]]; ?></option><?php
    }
    mysqli_free_result($result);
    ?>
                        </select>
		</div>
		<div class="group">
			<label>Status</label><br /> <select id="status" name="status">
				<option value="" selected>Completed & Running</option>
				<option value="all"
					<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "all") { echo "selected"; }?>>all</option>
				<option value="completed"
					<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "completed") { echo "selected"; }?>>completed</option>
				<option value="running"
					<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "running") { echo "selected"; }?>>running</option>
				<option value="error"
					<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "error") { echo "selected"; }?>>error</option>
				<option value="deleted"
					<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "deleted") { echo "selected"; }?>>deleted</option>
			</select>
		</div>
		<input type="button" value="FILTER"
			onclick="load<?php echo $tableDiv; ?>()" />

	</form>
</fieldset>

<?php include 'submitOnEnter.php'; ?>

<!-- Fast links -->


<script>
 function loadMy<?php echo $tableDiv; ?>() {                  		
     $.post("pages/tables/primary.php", {    	
        selectable: <?php echo $selectable; ?>,        
    	user_id: "<?php echo $_SESSION["hf_user_id"]; ?>",    	
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}

 function loadMyCompleted<?php echo $tableDiv; ?>() {                  		
     $.post("pages/tables/primary.php", {    	
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
     $.post("pages/tables/primary.php", {    	
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
			value="Completed" onclick="loadMyCompleted<?php echo $tableDiv; ?>()" />
		<input type="submit" value="Running"
			onclick="loadMyRunning<?php echo $tableDiv; ?>()" />
	</fieldset>
</div>


