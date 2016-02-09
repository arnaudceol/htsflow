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
 * limitations under the License.w
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


if (isset($_POST['primaryId']) && $_POST['primaryId'] != "") {
    $primaryIds = explode(" ", preg_replace('/\s+/', ' ',trim(strtoupper($_REQUEST['primaryId']))));
    $queryPrimaryId = "id IN (SELECT sample_id FROM primary_analysis WHERE id IN (" . implode(", ", $primaryIds) . "))";
    array_push($concatArray, $queryPrimaryId);
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

if (isset($_POST['description']) && $_POST['description'] != "") {
    $queryDescription = "id IN (SELECT sample_id FROM sample_description WHERE UPPER(description) like  '%" .strtoupper($_POST['description']) . "%') ";
    array_push($concatArray, $queryDescription);
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
// BS need a different assembly
$availableAssembliesBs= array();

foreach (scandir(GENOMES_FOLDER) as $assembly) {
    if ($assembly[0] != ".") {
    	if (strrpos($assembly, "_bs") > 0) {
    		$assemblyName = explode("_", $assembly) [0];
    		array_push($availableAssembliesBs, $assemblyName );
    	} else {
        	array_push($availableAssemblies, $assembly);
    	}
    }
}
 
// get available data types
$availableDataTypes = array();
$displayDataTypes = array();
$resultDataType = mysqli_query($con, "SELECT cv_term, display_term, available from controlled_vocabulary WHERE cv_type= 'data_type'");
while($dataTypeResult = mysqli_fetch_array($resultDataType)) {
    $term =  $dataTypeResult[0];
    $display =  $dataTypeResult[1];
    $isAvailable =  $dataTypeResult[2];
    $availableDataTypes[$term] = $isAvailable;
    $displayDataTypes[$term] = $display;
}
mysqli_free_result($resultDataType);


// Get list of samples with primary analyses
$sqlInPrimary = "SELECT DISTINCT sample_id FROM primary_analysis;";

$inPrimaryQuery = mysqli_query($con, $sqlInPrimary );
$inPrimaryIds = array();
while($inPrimaryResult = mysqli_fetch_array($inPrimaryQuery)) {
    $inPrimaryIds[] = $inPrimaryResult[0];
}
mysqli_free_result($inPrimaryQuery);


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
					<td align="left"><?php if ( true) {?><input type="checkbox"
					value="<?php echo $row["id"]; ?>" id="selected_<?php echo $row["id"]; ?>"
					name="selected_<?php echo $row["id"]; ?>"
					onclick="updateSelectedIds($(this).is(':checked'), '<?php echo $row["id"]; ?>', 'selectedIds')"
					<?php if (strpos($selectedIds,"'".$row['id']."'") !== false ) { echo "checked"; } ?> /> <?php  }?></td>				
				<?php } ?>
				<td class="centered"><?php echo $row["id"]; ?>
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
				
				if ($editable) { ?><a <?php echo $class; ?>  href='#'
									onclick='javascript:toggle("submitDescription_<?php echo $row["id"]; ?>")'></a><?php } ?>
										<form action="#" style="display: none; " class="popupstyle"
											id="submitDescription_<?php echo $row["id"]; ?>"
											method="POST">
											<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitDescription_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>
												<tbody style="vertical-align: top">
													<tr>
														<td ><textarea rows="30" cols="100" id="description_<?php echo $row["id"]; ?>"
																name="description" ><?php echo $sampleDescription; ?></textarea>
														</td>
														<td > 
														<a  class="filtertable" onclick="$('#description_<?php echo $row["id"]; ?>').val(sampleDescriptionTemplate);">fill with template</a>														
														<input type="submit" value="Submit"
															name="submitDescription" 
															 onclick="$.post('pages/sample/submitDescription.php', $('#submitDescription_<?php echo $row["id"]; ?>').serialize($('#submitDescription_<?php echo $row["id"]; ?>'))); refreshTable(); return false;" />
															 <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"></td>
													</tr>
												</tbody>
											</table>
										</form>
									
				</td>
				<td><table>
						<tbody>
							<tr>
								<td style="width: 10px"><?php if ($editable) { ?><a title="Edit" class="fa fa-pencil" href='#'
									onclick='javascript:toggle("submitSampleName_<?php echo $row["id"]; ?>")'></a><?php  } ?><form action="#"
										id="submitSampleName_<?php echo $row["id"]; ?>" style="display: none" class="popupstyle"
										method="post">								
											<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitSampleName_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2" name="TEXTdescription"
																style="width: 98%;"><?php echo trim($row["sample_name"]); ?></textarea>
														</td>
														<td><input type="submit" value="Submit"
															onclick="$.post('pages/sample/submitName.php', $('#submitSampleName_<?php echo $row["id"]; ?>').serialize($('#submitSampleName_<?php echo $row["id"]; ?>'))); refreshTable(); return false;"/>
															<input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>" /></td>
													</tr>
												</tbody>
											</table>
									</form></td>
								<td ><?php echo trim($row["sample_name"]) ; ?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>
					<table>
						<tbody>
							<tr><?php 
							$nonAvailableMessage = "";
							if (array_key_exists ($row["seq_method"], $displayDataTypes)) {
    							 $displayDataType = $displayDataTypes[$row["seq_method"]];
    							 if ( $availableDataTypes[$row["seq_method"]]  != 1) {
    							     $nonAvailableMessage = "<i title=\"Data type non available in HTS flow.\" class=\"fa fa-exclamation-triangle\"></i>";
    							 }
							} else {
							    $displayDataType = $row["seq_method"];
    							$nonAvailableMessage = "<i style=\"color: red\" title=\"Unknown data type, either it is not available in HTS-flow or it you should edit it.\" class=\"fa fa-exclamation-triangle\"></i>";
							}
							?><td style="width: 10px"><?php if ($editable) { ?><a  class="fa fa-pencil" href='#'
									title="Edit"  onclick='javascript:toggle("submitSeqMethod_<?php echo $row["id"]; ?>")'></a> <?php  }?>
									<form action="#"
										id="submitSeqMethod_<?php echo $row["id"]; ?>"
										method="post" style="display: none" class="popupstyle">
											<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitSeqMethod_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>
												<tbody>
													<tr>
														<td width="100%">
														<select name="TEXTdescription">
														<?php foreach ($displayDataTypes as $dataTypeOption => $displayDataTypeOption) {
															echo "<option value=\"". $dataTypeOption ."\">".$displayDataTypeOption."</option>";
														}?>
														</select></td>
														<td><input type="submit" value="Submit"
															onclick="$.post('pages/sample/submitSeqMethod.php', $('#submitSeqMethod_<?php echo $row["id"]; ?>').serialize($('#submitSeqMethod_<?php echo $row["id"]; ?>'))); refreshTable(); return false;" />
															<input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"/>						
															</td>
													</tr>
												</tbody>
											</table>
									</form></td>
								<td class="method"><?php echo $displayDataType; ?></td><td><?php 
								echo $nonAvailableMessage;?></td>
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
									title="Edit"  onclick='javascript:toggle("submitRefGenome_<?php echo $row["id"]; ?>")'></a><?php } ?>								
										<form action="#" style="display: none" class="popupstyle"
											id="submitRefGenome_<?php echo $row["id"]; ?>"
											method="POST">
										<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitRefGenome_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>
												<tbody>
													<tr>
														<td width="100%"><select name="TEXTdescription">
														<?php 
														if ($row["seq_method"] == 'BS-Seq') {
															$selectedAssemblies = $availableAssembliesBs;
														} else {
															$selectedAssemblies = $availableAssemblies;
														}
															
														foreach ($selectedAssemblies as $genome) {
															echo "<option value=\"". $genome ."\">".$genome."</option>";
														}?>
														</select>
														</td>
														<td><input type="submit" value="Submit"
															onclick="$.post('pages/sample/submitRefGenome.php', $('#submitRefGenome_<?php echo $row["id"]; ?>').serialize($('#submitRefGenome_<?php echo $row["id"]; ?>'))); refreshTable(); return false;"  /> 
															<input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>"></td>
													</tr>
												</tbody>
											</table>
										</form>
									</td>
								<td><?php echo $row["ref_genome"]; 
								    if (! in_array($row["ref_genome"], $selectedAssemblies )) {
								       ?> <i style="color: red" title="This genome is not available in HTS flow for this data type." class="fa fa-exclamation-triangle"></i><?php    
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
				        <a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('MERGE_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
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

