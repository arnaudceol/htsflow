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
     $.post("pages/tables/footprint_analysis.php", {    	
        selectable: <?php echo $selectable; ?>,
        type: "footprint_analysis",
    	//seq_method: $('#<?php echo $tableDiv; ?>Filter').find('#seq_method').find(":selected").val(),
    	user_id: $('#<?php echo $tableDiv; ?>Filter').find('#user_id').find(":selected").val(),
    	secondaryId: $('#<?php echo $tableDiv; ?>Filter').find('#secondaryId').val(),
        primaryId: $('#<?php echo $tableDiv; ?>Filter').find('#primaryId').val(),
        sampleId: $('#<?php echo $tableDiv; ?>Filter').find('#sampleId').val(),
    	//ref_genome: $('#<?php echo $tableDiv; ?>Filter').find('#ref_genome').find(":selected").val(),
    	//source: $('#<?php echo $tableDiv; ?>Filter').find('#source').find(":selected").val(),
    	selectedIds: $('#selectedIds').val() ,
     }, function(response) {
			    // 	Log the response to the console
      		    console.log("Response: "+response);
	    		$( "#<?php echo $tableDiv; ?>" ).html(response);
	});        
	}   
</script>

<form id="<?php echo $tableDiv; ?>Filter" name="CONTROL" >
	<table class="filtertable">
		<tbody>
			<tr>
				<th><b>Refine your searche:</b></th>
				<td>
				Secondary (peak) id: <input type="text" id="secondaryId" name="secondaryId" size="4" value="<?php if (isset( $_POST ["secondaryId"])) { echo  $_POST["secondaryId"]; } ?>"/>
				Primary id: <input type="text" id="primaryId" name="primaryId" size="4" value="<?php if (isset( $_POST ["primaryId"])) { echo  $_POST["primaryId"]; } ?>"/>
				Sample id: <input type="text" id="sampleId" name="sampleId" size="4" value="<?php if (isset( $_POST ["sampleId"])) { echo  $_POST["sampleId"]; } ?>"/>
				</td>
				<td>
			<?php
    $sql = "SELECT DISTINCT users.user_id, user_name FROM secondary_analysis, users WHERE users.user_id = secondary_analysis.user_id AND method = 'peak_calling' ORDER BY user_name ASC;";
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
				<td><input type="button" value="FILTER" onclick="load<?php echo $tableDiv; ?>()"/></td>
			</tr>
		</tbody>
	</table>
</form>
