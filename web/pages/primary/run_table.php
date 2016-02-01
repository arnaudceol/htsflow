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
// limit sql query results to speed up testing
//$sql_limit = " LIMIT 20";

// check for the presence of $_POST variables
$concatArray = array ();

$choosingString= "";
if (isset ( $_POST ['seq_method'] ) && $_POST ['seq_method'] != "") {
	$seq_method = "seq_method=\"" . $_POST ['seq_method'] . "\"";
} else {
	$seq_method = 0;
}
if (isset ( $_POST ['ref_genome'] ) && $_POST ['ref_genome'] != "") {
	$ref_genome = "ref_genome=\"" . trim($_POST ['ref_genome']) . "\"";
} else {
	$ref_genome = 0;
}
if (isset ( $_POST ['user_id'] ) && $_POST ['user_id'] != "") {
	$user_id = "user_id=\"" . $_POST ['user_id'] . "\"";
} else {
	$user_id = 0;
}



if (isset($_POST["submit"])) {

	if (sizeof ( $selectedSamples ) > 0) {
		$hasSelection = true;
		$sqlSelection = "";
		for($i = 0; $i < sizeof ( $selectedSamples ); $i ++) {
			
			if ($i == 0) {
				$sqlSelection .= " (id='" . $selectedSamples [$i] . "'";
			}
			if ($i != 0) {
				$sqlSelection .= " or id='" . $selectedSamples [$i] . "'";
			}
		}
		$sqlSelection .= ")";
		array_push ( $concatArray, $sqlSelection );
	}
}


array_push ( $concatArray, $seq_method );
array_push ( $concatArray, $ref_genome );
array_push ( $concatArray, $user_id );
$filtArray = array_filter ( $concatArray );
$numOfelements = count ( $filtArray );
global $con;
switch ($numOfelements) {
	case 0 :
		$sql = "SELECT * FROM sample s ORDER BY id DESC $sql_limit";
		$result = mysqli_query ( $con, $sql );
		break;
	case 1 :
		$sql = "SELECT * FROM sample s WHERE " . implode ( "", $filtArray ) . " ORDER BY id DESC $sql_limit";
		$result = mysqli_query ( $con, $sql );
		break;
	case 2 :
	case 3 :
		$sql = "SELECT * FROM sample s WHERE " . implode ( "AND ", $filtArray ) . " ORDER BY id DESC $sql_limit";
		$result = mysqli_query ( $con, $sql );
		break;
}

$numRighe = $result->num_rows;
if ($numRighe == 0) {
	?><p
	style="font-size: 1.2em; border: 2px dashed red; text-align: center; background-color: white;">
	<br />No samples found in the DB.<br /> <br />
</p><?php
} else {
	?>

	<div  class="table-container">
		<table border="1" width="100%" class="mytable filterable" id="sf">
			<thead>
				<tr><?php if (false == isset($_POST["submit"])) {?>
					<th></th><?php }?>
					<th>SAMPLE ID</th>
					<th>SAMPLE NAME</th>
					<th>METHOD</th>
					<th>READS LENGTH</th>
					<th>READS MODE</th>
					<th>REF GENOME</th>
					<th>READS DATA PATH</th>
					<th>PROJECT</th>
					<th>USER</th>
				</tr>
			</thead><?php
	while ( $row = mysqli_fetch_assoc ( $result ) ) {
		?><tr>
				<?php if (false == isset($_POST["submit"])) {?><td align="left"><input type="checkbox"
					name="selected_<?php echo $row["id"]; ?>"
					value="<?php echo $row["id"]; ?>" /></td><?php } else {?>
					<input type="hidden"
					name="selected_<?php echo $row["id"]; ?>"
					value="<?php echo $row["id"]; ?>" />
					<?php } ?>
				<td><?php echo $row["id"]; ?></td>
				<td>
					<table>
						<tbody>
							<tr>
								<td><a href='#'
									onclick='javascript:toggle("SAMPLENAME_<?php echo $row["id"]; ?>")'><img
										src="images/edit.png" /></a>
									<div id="SAMPLENAME_<?php echo $row["id"]; ?>"
										style="display: none" class="popupstyle">

										<table width="100%">
											<tbody>
												<tr>
													<form action=""
														name="submitSAMPLENAME_<?php echo $row["id"]; ?>"
														method="post">
														<td width="100%"><textarea rows="2" name="TEXTdescription"
																style="width: 98%;"><?php echo $row["sample_name"]; ?></textarea>
														</td>
														<td><input type="submit" value="Submit"
															name="submitSAMPLENAME" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>" /></td>
													</form>
												</tr>
											</tbody>
										</table>
									</div></td>
								<td><?php echo trim($row["sample_name"]); ?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>
					<table>
						<tbody>
							<tr>
								<td><a href='#'
									onclick='javascript:toggle("SEQMETHOD_<?php echo $row["id"]; ?>")'><img
										src="images/edit.png" /></a>
									<form action=""
										name="submitSEQMETHOD_<?php echo $row["id"]; ?>"
										method="post">
										<div id="SEQMETHOD_<?php echo $row["id"]; ?>"
											style="display: none" class="popupstyle">

											<table width="100%">
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2" name="TEXTdescription"
																style="width: 98%;"><?php echo $row ["seq_method"]; ?></textarea></td>
														<td><input type="submit" value="Submit"
															name="submitSEQMETHOD" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"></td>
													</tr>
												</tbody>
											</table>
										</div>
									</form></td>
								<td><?php echo $row["seq_method"]; ?></td>
							</tr>
						</tbody>
					</table>
				</td>


				<td align="left">
                                <?php echo $row["reads_length"]; ?>
                            </td>
				<td align="left">
                                <?php echo $row["reads_mode"]; ?>
                            </td>

				<td>
					<table>
						<tbody>
							<tr>
								<td><a href='#'
									onclick='javascript:toggle("REFGEN_<?php echo $row["id"]; ?>")'><img
										src="images/edit.png" /></a>
									<div id="REFGEN_<?php echo $row["id"]; ?>"
										style="display: none" class="popupstyle">
										<form action=""
											name="submitREFGENOME_<?php echo $row["id"]; ?>"
											method="POST">
											<table width="100%">
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2"
																name="REFGENOMEdescription" style="width: 98%;">
                                                                            <?php echo $row["ref_genome"]; ?>
                                                                        </textarea>

														</td>
														<td><input type="submit" value="Submit"
															name="submitREFGENOME" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"></td>
													</tr>
												</tbody>
											</table>
										</form>
									</div></td>
								<td><?php echo $row["ref_genome"]; ?></td>
							</tr>
						</tbody>
					</table>
				</td>

				<td><?php echo $row["raw_data_path"]; ?></td>
				<td><?php echo $row["project"]; ?></td>
				<td align="left"><?php echo $row["user_id"]; ?></td>
			</tr><?php
	}
	?></tbody>
		</table>
	</div>

	<?php if (false == isset($_POST["submit"])) { ?>
	<table>
		<tbody>
			<!--	        // IF YOU CHANGE THE NAME OF THE BUTTON REMEMBER TO CHANGE THE PRIMARY ANALYSIS DONE IN FUNCTION settingsTable()-->
			<!--	        // if ($elem != "PRIMARY ANALYSIS") <----- where here you have to put the name of the button -->
			<tr>
				<td colspan="17" align="left"><input type="submit" class="subButton"
					value="Settings" name="submit" /></td>
			</tr>
		</tbody>
	</table>
	<?php } ?>
<?php
}
