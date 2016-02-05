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


$querySpecPrepare = "SELECT *, CASE WHEN source = 0 THEN 'LIMS' WHEN source = 2 THEN 'EXTERNAL' WHEN source = 1 THEN 'MERGE' END AS source_type 
    FROM %s deg, secondary_analysis, primary_analysis, sample  
    WHERE sample_id = sample.id AND secondary_id = secondary_analysis.id AND primary_analysis.id= deg.primary_id AND secondary_id=%s;";

if (isset($_POST["type"])) {
	$type = $_POST["type"];
}

$concatArray = array();

if (isset($_POST['secondaryId']) && $_POST['secondaryId'] != "") {
    $querySecondaryId = " s.id = " . strtoupper($_POST['secondaryId']) ;
    array_push($concatArray, $querySecondaryId);
}

// Looking for sample ids. 
// First converti them to primary ids, and let continue to the 
// search by primary id section
$primaryIds = array();
if (isset($_REQUEST['sampleId']) && $_REQUEST['sampleId'] != "") {
    
    // there may be more than one sample: split the field
    $sampleIds = explode(" ", preg_replace('/\s+/', ' ',trim(strtoupper($_REQUEST['sampleId']))));
    $querySampleId = "SELECT id FROM primary_analysis WHERE UPPER(sample_id) IN ('" . implode("', '", $sampleIds) . "')";
    
    $primaryQuery = mysqli_query($con, $querySampleId );
    
    while($primaryResult = mysqli_fetch_array($primaryQuery)) {
        $primaryIds[] = $primaryResult[0];
    }
}


if (isset($_REQUEST['primaryId']) && $_REQUEST['primaryId'] != "") {   
     $primaryIds = array_merge($primaryIds , explode(" ", preg_replace('/\s+/', ' ',trim(strtoupper($_REQUEST['primaryId'])))));
}

// Looking for secondary based on a primary one?
if (sizeof($primaryIds) > 0) {
    $primaryIdSql = " s.id IN ";

    $primaryToSecondaySqls = array();

    $primaryId = implode(", ", $primaryIds);
    
    foreach (scandir("../secondary/") as $type) {
        if ($type != ".." && $type != "." && $type != 'common') {
            if (file_exists('../secondary/'. $type . '/primary_to_secondary.php')) {
                include '../secondary/'. $type . '/primary_to_secondary.php';
                array_push($primaryToSecondaySqls, $primaryToSecondarySql);
            } else {
                include '../secondary/common/primary_to_secondary.php';
                array_push($primaryToSecondaySqls, $primaryToSecondarySql);
            }
        }
    }

    $primaryIdSql .= "(" . implode ( " UNION ", $primaryToSecondaySqls ). ")";
    
    array_push($concatArray, $primaryIdSql);
}


if (isset($_POST['method']) && $_POST['method'] != "") {
    $queryMethod= "method =\"" . $_POST['method'] . "\"";
    array_push($concatArray, $queryMethod);
}


if (isset($_POST['user_id']) && $_POST['user_id'] != "") {
    $user_id = " us.user_id=" . $_POST['user_id'] . "";
    array_push($concatArray, $user_id);
}

if (isset($_POST['description']) && $_POST['description'] != "") {
    $queryDescription = "UPPER(description) like  '%" .strtoupper($_POST['description']) . "%'";
    array_push($concatArray, $queryDescription);
}

if (isset($_POST['title']) && $_POST['title'] != "") {
    $queryTitle = "UPPER(title) like  '%" .strtoupper($_POST['title']) . "%'";
    array_push($concatArray, $queryTitle);
}



if (isset($_POST['status']) && $_POST['status'] != "") {
    if ($_POST['status'] ==  "completed") {
        $status="status='completed'";
        array_push($concatArray, $status);
    } elseif ($_POST['status'] ==  "running") {
        $status="(status!='completed' AND status!='deleted' AND status NOT like 'Error%')";
        array_push($concatArray, $status);
    }  elseif ($_POST['status'] ==  "deleted") {
        $status="(status ='deleted')";
        array_push($concatArray, $status);
    } else { // error
        $status="status like 'Error%'";
        array_push($concatArray, $status);
    }
}



$baseQuery = "SELECT *,dateStart,  dateEnd, CASE WHEN status = 'completed' THEN timediff( dateEnd, dateStart )  ELSE timediff( NOW(), dateStart ) END AS time, us.user_name FROM secondary_analysis s, users us WHERE us.user_id = s.user_id" ;

$typeToQuery = array(
		'completed' => $baseQuery . " AND status='completed' ",
		'running' => $baseQuery . "  AND status!='completed' ",
		'all' => $baseQuery 
);

if (isset($type) && $type == 'running') {
	$sql = $typeToQuery[$type];
} else {
	$sql = $typeToQuery['all'];
}


$filtArray = array_filter($concatArray);
$numOfelements = count($filtArray);

switch ($numOfelements) {
    case 0:
        break;
    default:
        $sql = $sql . " AND " . implode(" AND ", $filtArray);
        break;
        //     default:
        //         $sql = "SELECT s.*, u.user_name FROM sample s, users u WHERE s.user_id = u.user_id AND " . implode(" AND ", $filtArray) . " ORDER BY id_sample DESC";
        //         break;
}

$result = mysqli_query($con, $sql  );
$count=$result->num_rows;
$result->close();
$numRighe= $count;
$result = mysqli_query($con, $sql . " ORDER BY dateStart DESC". $pagination);
?>

<div class="datagrid">
	<div class="table-container">
	<?php
	$tableDiv = "tableSecondary";
	$phpTable = "secondary.php";
	include 'browseScripts.php';
	?>
		<table class="mytable filterable" id="sf2" style="width: 100%;">
			<thead>
				<tr>
					<th>SECONDARY ID</th>
					<th style="width:0%;"></th>
					<th style="width: 100px; text-align:left" >TITLE</th>
					<th>METHOD</th>
					<th>DESCRIPTION</th>
					<th style="text-align:right">USER</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
    <?php
				$numRighe = $result->num_rows;
				
				// if the number of rows returned from the DB is 0, we have no
				// results, so we print only dashes. Values otherwise.
				if ($numRighe != 0) {					
					while ( $row = mysqli_fetch_assoc ( $result ) ) {
					
							?> <tr  <?php if ($row['status'] == "deleted") { echo " style=\"color: grey\""; }?>>
					<td class="centered"><?php echo $row["id"]; ?> <?php printStatus($row['status']); ?></td>
					<td><a id="ICON_<?php echo $row["id"]; ?>" class="fa fa-info"  href='#'
						onclick="javascript:toggle('OPTIONS_<?php echo $row["id"]; ?>', 'selectedIds');"></a>
						<div id="OPTIONS_<?php echo $row["id"]; ?>"
							style="display: none" class="popupstyle">							
							<a style="float: right;margin: 4px;" class="fa fa-times" href="#"  onclick="javascript:toggle('OPTIONS_<?php echo $row["id"]; ?>');"></a>
							<p><b>Start: </b><?php echo $row["dateStart"];  ?><br/>
							<b>End: </b><?php if ($row["dateEnd"] != "") { echo $row["dateEnd"]; } else {echo "-"; };  ?><br/>
							<b>Time (hh:mm:ss): </b><?php echo $row['time'];  ?><br/>
							</p><?php
							include '../secondary/' . $row ["method"] . '/detail_table.php'; ?>
						</div>
						<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/secondary/<?php  echo $row ["id"]; ?>/" ><i class="fa fa-folder"></i></a>	
					</td>
					<td>
					<table>
						<tbody>
							<tr>
								<?php if ($row["user_name"] == $_SESSION["hf_user_name"]) { ?><td style="width: 5px"><a class="fa fa-pencil" href='#'
									onclick='javascript:toggle("submitTitle_<?php echo $row["id"]; ?>")'></a><form action="#"
										id="submitTitle_<?php echo $row["id"]; ?>"
										method="post" style="display: none" class="popupstyle">
											<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitTitle_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>											
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2" name="TEXTtitle"
																style="width: 98%;"><?php echo trim($row["title"]); ?></textarea>
														</td>
														<td><input type="submit" value="Submit"
															onclick="$.post('pages/secondary/common/submitTitle.php', $('#submitTitle_<?php echo $row["id"]; ?>').serialize($('#submitTitle_<?php echo $row["id"]; ?>'))); refreshTable(); return false;"   />
															<input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>" /></td>
													</tr>
												</tbody>
											</table>
									</form></td><?php  } ?>
								<td ><?php echo trim($row["title"]) ; ?></td>
							</tr>
						</tbody>
					</table>
					</td>
					<td class="centered"><?php echo $row["method"]; ?></td>		
					<td><?php echo $row['description']; ?>
							<?php if ($row["user_name"] == $_SESSION["hf_user_name"]) { ?><a class="fa fa-pencil" href='#'
									onclick='javascript:toggle("submitDescription_<?php echo $row["id"]; ?>")'></a><form action="#"
										id="submitDescription_<?php echo $row["id"]; ?>"
										method="post"	style="display: none" class="popupstyle">
											<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitDescription_<?php echo $row["id"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>
												<tbody>
													<tr>
														<td width="100%"><textarea rows="2" name="TEXTdescription"
																style="width: 98%;"><?php echo trim($row["description"]); ?></textarea>
														</td>
														<td><input type="submit" value="Submit"
															onclick="$.post('pages/secondary/common/submitDescription.php', $('#submitDescription_<?php echo $row["id"]; ?>').serialize($('#submitDescription_<?php echo $row["id"]; ?>'))); refreshTable(); return false;"   />
															 <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>" /></td>
													</tr>
												</tbody>
											</table>
									</form><?php  } ?>
					</td>			
					<td style="text-align: right"><?php echo $row["user_name"]; ?><a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/users/<?php 
					   echo $row["user_name"];  
					   echo "/S";
					   echo $row ["id"];
				    ?>" ><i class="fa fa-newspaper-o"></i></a>
				    </td>
				    <td width="10px">
					<?php
					$allowUserDelete = false;
					$allowAdminDelete = false;
					if ($_SESSION['grantedAdmin'] == 1  && $row['status'] != 'deleted') {
					    $allowAdminDelete = true;
					}
					if ($row["user_name"] == $_SESSION["hf_user_name"] 
					    &&  ($row['status'] == 'completed' || strpos($row['status'], 'Error') === 0)) {
					    $allowUserDelete = true;
					}
					
					if ($allowUserDelete || $allowAdminDelete) { ?>
					<a style="float: right;margin: 4px;<?php if (!$allowUserDelete) { echo "color: red"; } ?>" class="fa fa-eraser" href="#"  onclick="$.post('pages/secondary/common/removeSecondaryForm.php', {id: '<?php echo $row['id']; ?>', }, function(response) { $( '#DELETE_<?php echo $row["id"]; ?>_FORM' ).html(response);});javascript:toggle('DELETE_<?php echo $row["id"]; ?>');"></a>
					<div id="DELETE_<?php echo $row["id"]; ?>"
							style="display: none" class="popupstyle">							
							<div style="display: inline" id="DELETE_<?php echo $row["id"]; ?>_FORM"></div>
							<input type="submit" value="Cancel"  onclick="javascript:toggle('DELETE_<?php echo $row["id"]; ?>')"/>
									</div>
						<?php } 
						if (($_SESSION['grantedAdmin'] == 1 || $row["user_name"] == $_SESSION["hf_user_name"])
					        && $row['status'] == 'deleted') { ?>				
					<a style="float: right;margin: 4px;" class="fa fa-repeat" href="#"  onclick="javascript:toggle('REPEAT_<?php echo $row["id"]; ?>');"></a>
					<div id="REPEAT_<?php echo $row["id"]; ?>"
							style="display: none" class="popupstyle">
							<b>Repeat primary analysis: </b><?php echo $row['id']; ?>? <br/>
							<form style=" display: inline" action="pages/secondary/common/submitRepeat.php"
										name="submitRepeat_<?php echo $row["id"]; ?>"
										method="post"><input type="submit" value="Confirm"
															name="submitRepeatSecondary" /> <input type="hidden" name="ID"
															value="<?php echo $row["id"]; ?>" />

									</form><input type="submit" value="Cancel"  onclick="javascript:toggle('DELETE_<?php echo $row["id"]; ?>')"/>
									</div>
									<?php  }?>	
					
					</td>

				</tr>           <?php
						}
					$result->close();
				} else {
					?><tr>
					<td>-</td>
					<td>-</td>
					<td>-</td>
					<td>-</td>
					<td>-</td>
					<td>-</td>
					<td>-</td>
				</tr><?php
				}
				?>				
				</tbody>
		</table>
	</div>
</div>