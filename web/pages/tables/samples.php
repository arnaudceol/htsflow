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

session_start();
include 'tableCommons.php';
$concatArray = array();

if (isset($_POST['seq_method']) && $_POST['seq_method'] != "") {
    $seq_method = "seq_method=\"" . $_POST['seq_method'] . "\"";
    array_push($concatArray, $seq_method);
}

if (isset($_POST['ref_genome']) && $_POST['ref_genome'] != "") {
    $ref_genome = "ref_genome=\"" . trim($_POST['ref_genome']) . "\"";
    array_push($concatArray, $ref_genome);
}

if (isset($_POST['user_id']) && $_POST['user_id'] != "") {
    $user_id = "users.user_id=" . $_POST['user_id'] . "";
    array_push($concatArray, $user_id);
}

if (isset($_POST['source']) && $_POST['source'] != "") {
    $source = "source=" . $_POST['source'] . "";
    array_push($concatArray, $source);
} 

if (isset($_POST['sampleId']) && $_POST['sampleId'] != "") {
    $querySampleId = "UPPER(sample.id) =\"" . strtoupper($_POST['sampleId']) . "\"";
    array_push($concatArray, $querySampleId);
}

if (isset($_POST['sampleName']) && $_POST['sampleName'] != "") {
    $querySampleName = "UPPER(sample_name) like \"%" . strtoupper($_POST['sampleName']) . "%\"";
    array_push($concatArray, $querySampleName);
}

if (isset($selectedSamples) && sizeof($selectedSamples) > 0) {
    $hasSelection = true;
    $sqlSelection = "";
    for ($i = 0; $i < sizeof($selectedSamples); $i ++) {
        
        if ($i == 0) {
            $sqlSelection .= " (sample.id='" . $selectedSamples[$i] . "'";
        }
        if ($i != 0) {
            $sqlSelection .= " or sample.id='" . $selectedSamples[$i] . "'";
        }
    }
    $sqlSelection .= ")";
    array_push($concatArray, $sqlSelection);
}


// Do not show merged in samples
// array_push($concatArray, " source <> 1 ");

$filtArray = array_filter($concatArray);
$numOfelements = count($filtArray);
global $con;
switch ($numOfelements) {
    case 0:
        $sql = "SELECT sample.*, user_name FROM sample, users WHERE sample.user_id = users.user_id ORDER BY raw_data_path_date DESC";
        break;
    case 1:
        $sql = "SELECT sample.*, user_name FROM sample, users WHERE sample.user_id = users.user_id AND " . implode("", $filtArray) . " ORDER BY raw_data_path_date DESC";
        break;
    default:
        $sql = "SELECT sample.*, user_name FROM sample, users WHERE sample.user_id = users.user_id AND " . implode(" AND ", $filtArray) . " ORDER BY raw_data_path_date DESC";
        break;
}

$result = mysqli_query($con, "SELECT COUNT(*) FROM (" . $sql . ") as g");
error_log($sql);
$count = $result->fetch_row();
$result->close();
$numRighe = $count[0];
$result = mysqli_query($con, $sql . $pagination);

// Get available genomes:
$availableAssemblies= array();
foreach (scandir(GENOMES_FOLDER) as $assembly) {
    if ($assembly != "." && $type != "..") {
        array_push($availableAssemblies, $assembly);
    }
}
 

?>
<div class="datagrid">
	<div class="table-container">
	<?php
$tableDiv = "sampleTable";
$phpTable = "samples.php";
include 'browseScripts.php';
$indx = rand();


?>
<?php  $sampleDescriptionTemplate = file_get_contents('../../sample_template.txt'); ?>
<script>
sampleDescriptionTemplate = '<?php echo str_replace("\n", '\n\'+\'',  str_replace("'", "\\\'",$sampleDescriptionTemplate)); ?>';
</script>

		<table class="mytable filterable" id="sf<?php echo $indx; ?>">
			<thead>
				<tr><?php if ($selectable) {?>
					<th></th><?php }?>
					<th class="centered">SAMPLE ID</th>
					<th class="centered"></th>
					<th class="centered">SAMPLE NAME</th>
					<th class="centered">METHOD</th>
					<th class="centered">READS LENGTH</th>
					<th class="centered">READS MODE</th>
					<th class="centered">REF GENOME</th>
					<th class="centered">READS DATA PATH</th>
					<th class="centered">CREATED</th>					 
					<th class="centered">SOURCE</th>
					<th class="centered">USER</th>
				</tr>
			</thead><?php

while ($row = mysqli_fetch_assoc($result)) {
    
    ?><tr>
				<?php if ($selectable ) {?>
					<td align="left"><?php if ( in_array($row["ref_genome"],$availableAssemblies )) {?><input type="checkbox"
					value="<?php echo $row["id"]; ?>" id="selected_<?php echo $row["id"]; ?>"
					name="selected_<?php echo $row["id"]; ?>"
					onclick="updateSelectedIds($(this).is(':checked'), '<?php echo $row["id"]; ?>', 'selectedIds')"
					<?php if (strpos($selectedIds,"'".$row['id']."'") !== false ) { echo "checked"; } ?> /> <?php  }?></td>				
				<?php } ?>
				<td class="centered"><?php echo $row["id"]; ?>
				<a href="primary-browse.php?sampleId=<?php  echo $row ["id"]; ?>" target="_blank"><i title="Go to primary analyses" class="fa fa-share"></i></a>
				</td>
				<td><?php 
				$sampleDescription = "";
				$queryDescription = sprintf("SELECT description FROM sample_description WHERE sample_id = '%s'", $row["id"]);				
				$resDescription = mysqli_query($con, $queryDescription);
				if (mysqli_num_rows($resDescription) >= 1) {
				    $sampleDescription =  mysqli_fetch_assoc($resDescription)["description"];
				    if ($sampleDescription != '') {
				        $class = "title=\"sample description, click to edit\" class=\"fa fa-file-text-o\" style=\"color: green\"";				        
				    } else {
				        $class = "title=\"sample description missing, click to edit\" class=\"fa fa-file-o\" style=\"color: red\""; 
				    }
				} else {
				        $class = "title=\"sample description missing, click to edit\" class=\"fa fa-file-o\" style=\"color: red\""; 
				}
				?>
				
				<?php if ($editable && ($_SESSION['grantedAdmin'] == 1  || $row["user_name"] == $_SESSION["hf_user_name"])) { ?><a <?php echo $class; ?>  href='#'
									onclick='javascript:toggle("REFGEN_<?php echo $row["id"]; ?>")'></a><?php } ?>
									<div id="REFGEN_<?php echo $row["id"]; ?>"
										style="display: none; " class="popupstyle">
										<form action=""
											name="submitDescription_<?php echo $row["id"]; ?>"
											method="POST">
											<table>
												<tbody style="vertical-align: top">
													<tr>
														<td ><textarea rows="30" cols="100" id="description_<?php echo $row["id"]; ?>"
																name="description" ><?php echo $sampleDescription; ?></textarea>
														</td>
														<td > 
														<a  class="filtertable" onclick="$('#description_<?php echo $row["id"]; ?>').val(sampleDescriptionTemplate)">fill with template</a>														
														<input type="submit" value="Submit"
															name="submitDescription" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"></td>
													</tr>
												</tbody>
											</table>
										</form>
									</div>
				</td>
				<td><table>
						<tbody>
							<tr>
								<td style="width: 10px"><?php if ($editable && ($_SESSION['grantedAdmin'] == 1  || $row["user_name"] == $_SESSION["hf_user_name"])) { ?><a title="Edit" class="fa fa-pencil" href='#'
									onclick='javascript:toggle("SAMPLENAME_<?php echo $row["id"]; ?>")'></a><?php  } ?><form action=""
										name="submitSAMPLENAME_<?php echo $row["id"]; ?>"
										method="post"><div id="SAMPLENAME_<?php echo $row["id"]; ?>"
											style="display: none" class="popupstyle"><table>
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2" name="TEXTdescription"
																style="width: 98%;"><?php echo trim($row["sample_name"]); ?></textarea>
														</td>
														<td><input type="submit" value="Submit"
															name="submitSAMPLENAME" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>" /></td>
													</tr>
												</tbody>
											</table>
										</div>
									</form></td>
								<td ><?php echo trim($row["sample_name"]) ; ?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>
					<table>
						<tbody>
							<tr>
								<td style="width: 10px"><?php if ($editable && ($_SESSION['grantedAdmin'] == 1  || $row["user_name"] == $_SESSION["hf_user_name"])) { ?><a  class="fa fa-pencil" href='#'
									title="Edit"  onclick='javascript:toggle("SEQMETHOD_<?php echo $row["id"]; ?>")'></a> <?php  }?>
									<form action=""
										name="submitSEQMETHOD_<?php echo $row["id"]; ?>"
										method="post">
										<div id="SEQMETHOD_<?php echo $row["id"]; ?>"
											style="display: none" class="popupstyle">
											<table>
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
								<td class="method"><?php echo $row["seq_method"]; ?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td class="centered"><?php echo $row["reads_length"]; ?></td>
				<td class="centered"><?php echo $row["reads_mode"]; ?></td>
				<td>
					<table>
						<tbody>
							<tr>
								<td style="width: 10px"><?php if ($editable && ($_SESSION['grantedAdmin'] == 1  || $row["user_name"] == $_SESSION["hf_user_name"])) { ?><a  class="fa fa-pencil"  href='#'
									title="Edit"  onclick='javascript:toggle("REFGEN_<?php echo $row["id"]; ?>")'></a><?php } ?>
									<div id="REFGEN_<?php echo $row["id"]; ?>"
										style="display: none" class="popupstyle">
										<form action=""
											name="submitREFGENOME_<?php echo $row["id"]; ?>"
											method="POST">
											<table>
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2"
																name="REFGENOMEdescription" style="width: 98%;"><?php echo $row["ref_genome"]; ?></textarea>
														</td>
														<td><input type="submit" value="Submit"
															name="submitREFGENOME" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"></td>
													</tr>
												</tbody>
											</table>
										</form>
									</div></td>
								<td><?php echo $row["ref_genome"]; 
								    if (! in_array($row["ref_genome"], $availableAssemblies )) {
								       ?> <i style="color: red" title="This genome is not available in HTS flow." class="fa fa-exclamation-triangle"></i><?php    
								    }
								?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td><?php echo $row["raw_data_path"]; ?></td>
				<td><?php
				    if ($row["raw_data_path_date"] != "") {
				        echo $row["raw_data_path_date"];
				    } else {
                        echo "N/A";   
                    }
                ?></td>
				<td class="centered"><?php 
				    echo $mergArr[$row["source"]];
				    if ($row["source"] == 1) {
				        ?>
				        <a  href="#" title="See merged samples" class="fa fa-info" onclick="javascript:toggle('MERGE_<?php echo $row["id"]; ?>');"></a>
				        <div id="MERGE_<?php echo $row["id"]; ?>" style="display: none" class="popupstyle">
				        	<?php
				        	   $sampleId = $row["id"];
				        	   include 'mergeDetails.php';
				        	?>
				        </div>
				        <?php 
				    }
				?></td>
				<td class="centered"><?php echo $row["user_name"]; ?></td>
			</tr><?php
}
?></tbody>
		</table>
	</div>
</div>

