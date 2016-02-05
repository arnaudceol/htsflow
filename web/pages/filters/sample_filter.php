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
<script>
 function load<?php echo $tableDiv; ?>() {
     $.post("pages/tables/samples.php", {    	 
    	editable: <?php  if ($editable) { echo "true"; } else { echo "false"; }?> ,
        selectable: <?php  if ($selectable) { echo "true"; } else { echo "false"; }?> ,
     	sampleId: $('#<?php echo $tableDiv; ?>Filter').find('#sampleId').val(),
     	primaryId: $('#<?php echo $tableDiv; ?>Filter').find('#primaryId').val(),
     	sampleName: $('#<?php echo $tableDiv; ?>Filter').find('#sampleName').val(),
    	description: $('#<?php echo $tableDiv; ?>Filter').find('#description').val(),
    	seq_method: $('#<?php echo $tableDiv; ?>Filter').find('#seq_method').find(":selected").val(),
    	user_id: $('#<?php echo $tableDiv; ?>Filter').find('#user_id').find(":selected").val(),
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

<form id="<?php echo $tableDiv; ?>Filter" name="CONTROL">
	<!--	    // FILTERING OPTIONS-->
		<fieldset class="filtertable">
    <legend >Filter table</legend>	
	<table>
		<tbody>
			<tr>
<!-- 				<th><b>Refine your searche:</b></th> -->
				<td>
				Sample id: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="sampleId" name="sampleId" size="5" value="<?php if (isset( $_POST ["sampleId"])) { echo  $_POST["sampleId"]; } ?>"/>
				Primary id: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="primaryId" name="primaryId" size="5" value="<?php if (isset( $_POST ["primaryId"])) { echo  $_POST["primaryId"]; } ?>"/>
				Sample name: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="sampleName" name="sampleName" size="10" value="<?php if (isset( $_POST ["sampleName"])) { echo  $_POST["sampleName"]; } ?>"/>
				Description: <input class="<?php echo $tableDiv; ?>SubmitKey" type="text" id="description" name="description" size="10" value="<?php if (isset( $_POST ["description"])) { echo  $_POST["description"]; } ?>"/>
<?php
$sql = "SELECT DISTINCT seq_method FROM sample WHERE  source <> 1 ORDER BY seq_method ASC;";

$result = mysqli_query($con, $sql);
?><select id="seq_method" name="seq_method">
						<option value="" selected>All sequencing method</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["seq_method"]; ?>"
							<?php if (isset ( $_POST ['seq_method'] ) && $_POST ['seq_method'] == $row["seq_method"]) { echo "selected"; }?>><?php echo $row["seq_method"]; ?></option><?php
    }
    ?>
                        </select>

				</td>
				<td><?php
    $sql = "SELECT DISTINCT sample.user_id, user_name FROM sample, users WHERE users.user_id = sample.user_id AND source <> 1 ORDER BY user_name ASC;";
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
				<td><?php
    $sql = "SELECT DISTINCT ref_genome FROM sample WHERE  source <> 1 AND ref_genome <> '' ORDER BY ref_genome ASC;";
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
                        </select></td>
				<td><?php
    $sql = "SELECT DISTINCT source FROM sample  ORDER BY source ASC;";
    $result = mysqli_query($con, $sql);
    ?>
                        <select id="source" name="source">
						<option value="" selected>Source</option><?php
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
				<td><input type="button" value="FILTER"
					onclick="load<?php echo $tableDiv; ?>()" /></td>
			</tr>
		</tbody>
	</table>
	</fieldset>
</form>



<?php include 'submitOnEnter.php'; ?>
