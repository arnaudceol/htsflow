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
     $.post("pages/tables/primary.php", {    	
        selectable: <?php echo $selectable; ?>,
        primaryId: $('#<?php echo $tableDiv; ?>Filter').find('#primaryId').val(),
        sampleId: $('#<?php echo $tableDiv; ?>Filter').find('#sampleId').val(),
       	sampleName: $('#<?php echo $tableDiv; ?>Filter').find('#sampleName').val(),
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
	<table class="filtertable">
		<tbody>
			<tr>
				<td>
				Primary id: <input type="text" id="primaryId" name="primaryId" size="4" value="<?php if (isset( $_POST ["primaryId"])) { echo  $_POST["primaryId"]; } ?>"/>
				Sample id: <input type="text" id="sampleId" name="sampleId" size="4" value="<?php if (isset( $_POST ["sampleId"])) { echo  $_POST["sampleId"]; } ?>"/>
				Sample name: <input type="text" id="sampleName" name="sampleName" size="5" value="<?php if (isset( $_POST ["sampleName"])) { echo  $_POST["sampleName"]; } ?>"/>
				</td>
				<td><?php
				$sql = "SELECT DISTINCT users.user_id, user_name FROM primary_analysis, users WHERE users.user_id = primary_analysis.user_id ORDER BY user_name ASC;";
				$result = mysqli_query ( $con, $sql );
				?>
                        <select id="user_id" name="user_id">
						<option value="" selected>All user</option>
						<?php
						while ( $row = mysqli_fetch_assoc ( $result ) ) {
							?><option value="<?php echo $row["user_id"]; ?>"
							<?php if (isset ( $_POST ['user_id'] ) && $_POST ['user_id'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
						}
						mysqli_free_result($result);
						?>
                        </select></td>
				<td><?php
				$sql = "SELECT DISTINCT ref_genome FROM sample , primary_analysis  WHERE sample_id = sample.id AND source <> 1 ORDER BY ref_genome ASC;";
				$result = mysqli_query ( $con, $sql );
				?>
                        <select id="ref_genome" name="ref_genome">
						<option value="" selected>All reference genomes</option>
						<?php
						while ( $row = mysqli_fetch_assoc ( $result ) ) {
							?><option value="<?php echo $row["ref_genome"]; ?>"
							<?php if (isset ( $_POST ['ref_genome'] ) && $_POST ['ref_genome'] == $row["ref_genome"]) { echo "selected"; }?>><?php echo $row["ref_genome"]; ?></option><?php
						}
						mysqli_free_result($result);
						?>
                        </select></td>
				<td><?php
				$sql = "SELECT DISTINCT source FROM sample ORDER BY source ASC;";
				$result = mysqli_query ( $con, $sql );
				?>
                        <select id="source" name="source">
						<option value="" selected>All sources</option>
						<?php
						$mergArr = array (
								0 => "<span>LIMS</span>",
								1 => "<span>MERGE</span>",
								2 => "<span>OUTER</span>" 
						);
						while ( $row = mysqli_fetch_assoc ( $result ) ) {
							?><option value="<?php echo $row["source"]; ?>"
							<?php if (isset ( $_POST ['source'] ) && $_POST ['source'] == $row["source"]) { echo "selected"; }?>><?php echo $mergArr[$row["source"]]; ?></option><?php
						}
						mysqli_free_result($result);
						?>
                        </select></td>
				<td><input type="button" value="FILTER"
					onclick="load<?php echo $tableDiv; ?>()" /></td>
			</tr>
		</tbody>
	</table>
</form>
