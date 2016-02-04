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
    	ref_genome: $('#<?php echo $tableDiv; ?>Filter').find('#ref_genome').find(":selected").val(),
    	source: $('#<?php echo $tableDiv; ?>Filter').find('#source').find(":selected").val(),
    	selectedIds: $('#selectedIds').val() ,
     }, function(response) {
			    // 	Log the response to the console
      		    //  console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}
</script>

<form id="<?php echo $tableDiv; ?>Filter" name="CONTROL" >
	<!--	    // FILTERING OPTIONS-->
	<table class="filtertable">
		<tbody>
			<tr>
				<td>Primary id: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="primaryId" name="primaryId"
					size="4"
					value="<?php if (isset( $_POST ["primaryId"])) { echo  $_POST["primaryId"]; } ?>" />
					Sample id: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="sampleId" name="sampleId"
					size="4"
					value="<?php if (isset( $_POST ["sampleId"])) { echo  $_POST["sampleId"]; } ?>" />
					Sample name: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="sampleName" name="sampleName"
					size="5"
					value="<?php if (isset( $_POST ["sampleName"])) { echo  $_POST["sampleName"]; } ?>" />
					Description: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="description" name="description" size="10" value="<?php if (isset( $_POST ["description"])) { echo  $_POST["description"]; } ?>"/>
				
<?php
$sql = "SELECT DISTINCT seq_method FROM sample, primary_analysis  WHERE sample_id = sample.id AND source <> 1 ORDER BY seq_method ASC;";

$result = mysqli_query($con, $sql);
?><select id="seqMethod" name="seqMethod">
						<option value="" selected>All sequencing method</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["seq_method"]; ?>"
							<?php if (isset ( $_POST ['seqMethod'] ) && $_POST ['seqMethod'] == $row["seq_method"]) { echo "selected"; }?>><?php echo $row["seq_method"]; ?></option><?php
    }
    ?>
                        </select>

				</td>
				<td><?php
    $sql = "SELECT DISTINCT users.user_id, user_name FROM primary_analysis, users WHERE users.user_id = primary_analysis.user_id ORDER BY user_name ASC;";
    $result = mysqli_query($con, $sql);
    ?>
                        <select id="user_id" name="user_id">
						<option value="" selected>All user</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["user_id"]; ?>"
							<?php if (isset ( $_POST ['user_id'] ) && $_POST ['user_id'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
    }
    ?>
                        </select></td>
				<td>
				
				<td><?php
    $sql = "SELECT DISTINCT users.user_id, user_name FROM sample, users, primary_analysis WHERE sample_id = sample.id AND users.user_id = sample.user_id ORDER BY user_name ASC;";
    $result = mysqli_query($con, $sql);
    ?>
                        <select id="sample_owner" name="sample_owner">
						<option value="" selected>All sample submitters</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php
        
echo $row["user_id"];
        ?>"
							<?php if (isset ( $_POST ['sample_owner'] ) && $_POST ['sample_owner'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
    }
    ?>
                        </select></td>
				<td>
				
				
				<?php
    $sql = "SELECT DISTINCT ref_genome FROM sample , primary_analysis  WHERE sample_id = sample.id AND source <> 1 ORDER BY ref_genome ASC;";
    $result = mysqli_query($con, $sql);
    ?>
                        <select id="ref_genome" name="ref_genome">
						<option value="" selected>All reference genomes</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["ref_genome"]; ?>"
							<?php if (isset ( $_POST ['ref_genome'] ) && $_POST ['ref_genome'] == $row["ref_genome"]) { echo "selected"; }?>><?php echo $row["ref_genome"]; ?></option><?php
    }
    ?>
                        </select>
				</td>
				<td><?php
    $sql = "SELECT DISTINCT source FROM sample ORDER BY source ASC;";
    $result = mysqli_query($con, $sql);
    ?>
                        <select id="source" name="source">
						<option value="" selected>All sources</option>
						<?php
    $mergeOptions = array(
        0 => "<span>lims</span>",
        1 => "<span>merged</span>",
        2 => "<span>external</span>"
    );
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["source"]; ?>"
							<?php if (isset ( $_POST ['source'] ) && $_POST ['source'] == $row["source"]) { echo "selected"; }?>><?php echo $mergeOptions[$row["source"]]; ?></option><?php
    }
    ?>
                        </select></td>
                   <td><select id="status" name="status">
						<option value="" selected>All status</option>
						<option value="completed" <?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "completed") { echo "selected"; }?>>completed</option>
						<option value="running" <?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "running") { echo "selected"; }?>>running</option>
						<option value="error" <?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "error") { echo "selected"; }?>>error</option>
						<option value="deleted" <?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "error") { echo "selected"; }?>>deleted</option>
                        </select></td>
				<td><input type="button" value="FILTER"
					onclick="load<?php echo $tableDiv; ?>()" /></td>
			</tr>
		</tbody>
	</table>
</form>

<?php include 'submitOnEnter.php'; ?>

<!-- Fast links -->
<div style="display: inline">

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
<input type="submit" value="My analysis" onclick="loadMy<?php echo $tableDiv; ?>()"/>
<input type="submit" value="My completed analysis" onclick="loadMyCompleted<?php echo $tableDiv; ?>()"/>
<input type="submit" value="My running analysis" onclick="loadMyRunning<?php echo $tableDiv; ?>()"/>

</div>


