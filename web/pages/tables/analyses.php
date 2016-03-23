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

$queryPrimary = "SELECT primary_analysis.created, primary_analysis.id, dateStart, " .
  " dateEnd, CASE WHEN status = 'completed' THEN timediff( dateEnd, dateStart )  ELSE timediff( NOW(), dateStart ) END AS time,   " .
  " users.user_id, user_name, 'primary' as level, seq_method as method, sample_name as description, status   " .
  " FROM primary_analysis, sample, users  WHERE sample_id = sample.id AND primary_analysis.user_id = users.user_id ";

$querySecondary = "SELECT secondary_analysis.created, id, dateStart, dateEnd, 
    CASE WHEN status = 'completed' THEN timediff( dateEnd, dateStart ) ELSE timediff( NOW(), dateStart ) END AS time, 
    users.user_id, user_name, 'secondary' as type, method as subtype, description, status  
    FROM secondary_analysis, users  
    WHERE secondary_analysis.user_id = users.user_id"; 

$queryOther = "SELECT other_analysis.created, id, dateStart, dateEnd,
    CASE WHEN status = 'completed' THEN timediff( dateEnd, dateStart ) ELSE timediff( NOW(), dateStart ) END AS time,
    users.user_id, user_name, 'other' as type, 'other' as subtype, description, status
    FROM other_analysis, users
    WHERE other_analysis.user_id = users.user_id";



$baseQuery = $queryPrimary . " UNION " . $querySecondary. " UNION " . $queryOther;

$concatArray = array();

if (isset($_POST['status']) && $_POST['status'] != "") {
    if ($_POST['status'] ==  "completed") {
        $status="status='completed'";
        array_push($concatArray, $status);
    } elseif ($_POST['status'] ==  "running") {
        $status="(status!='completed' AND status NOT like 'Error%')";
        array_push($concatArray, $status);
    } else { // error
        $status="status like 'Error%'";
        array_push($concatArray, $status);
    }
}


if (isset($_POST['method']) && $_POST['method'] != "") {
    $method = "method='" . $_POST['method'] . "'";
    array_push($concatArray, $method);
}

if (isset($_POST['user_id']) && $_POST['user_id'] != "") {    
     $user_id = "user_id=" . $_POST['user_id'] . "";
     array_push($concatArray, $user_id);
} elseif ( $_SESSION['grantedAdmin'] == 1 ) { 
    $user_id = "user_id=" . $_SESSION["hf_user_id"] . "";
    array_push($concatArray, $user_id);
}


if (isset($_POST['level']) && $_POST['level'] != "") {
	$level = "level='" . $_POST['level'] . "'";
	array_push($concatArray, $level);
} 

$filtArray = array_filter($concatArray);
$numOfelements = count($concatArray);

switch ($numOfelements) {
    case 0:
        $sql = "SELECT * FROM (" . $baseQuery . ") as g ORDER BY dateStart DESC";
        break;
    case 1:
        $sql = "SELECT * FROM (" . $baseQuery . ") as g WHERE " . implode("", $filtArray) . " ORDER BY dateStart DESC";
        break;
    default:
        $sql = "SELECT * FROM (" . $baseQuery . ") as g WHERE " . implode(" AND ", $filtArray) . " ORDER BY dateStart DESC";
        break;
}

error_log( "SELECT COUNT(*) FROM (". $sql . ") as g" );
$result = mysqli_query($con, $sql );

$count=$result->num_rows;
$result->close();
$numRighe= $count;
$result = mysqli_query($con, $sql . $pagination);
?>
<div class="datagrid">
	<div class="table-container">
	<?php
	$tableDiv = "tableAnalysis";
    $phpTable = "analyses.php";
	include 'browseScripts.php';
	?>
		<table class="mytable filterable" id="sf2" style="width: 100%;">
			<thead>
				<tr>
					<th>ID</th>
					<th style="width:0%;"></th>
					<th>LEVEL</th>
					<th>METHOD</th>
					<th>START</th>
					<th>END/TIME (hh:mm:ss)</th>					
					<th>STATUS</th>
					<?php if ( $_SESSION['grantedAdmin'] == 1 ) {?><th>user</th><?php  } ?>
				</tr>
			</thead>
			<tbody>
    <?php
				$numRighe = $result->num_rows;
				
				// if the number of rows returned from the DB is 0, we have no
				// results, so we print only dashes. Values otherwise.
				if ($numRighe != 0) {					
					while ( $row = mysqli_fetch_assoc ( $result ) ) {
					
							?> <tr>
					<td><?php echo $row["id"]; ?> <?php printStatus($row['status']); ?></td>
					<td>
					<a id="ICON_<?php echo $row["id"]; ?>" class="fa fa-" href="#"  onclick="javascript:toggle('OPTIONS_<?php echo $row["id"]; ?>'); "></a>
						<div id="OPTIONS_<?php echo $row["id"]; ?>"
							style="display: none" class="popupstyle">
							<a style="float: right;margin: 4px;" class="fa fa-times" href="#"  onclick="javascript:toggle('OPTIONS_<?php echo $row["id"]; ?>'); "></a>
							<?php 
						if ($row["level"] == 'secondary') {?>
							<?php
							include '../secondary/' . $row ["method"] . '/detail_table.php';
							?>
							
						<?php }  else if ($row["level"] == 'secondary') {
							  // primary
							    $convArr = array(
							        1 => "<span style=\"color: #008000;font-weight:bold\">OK</span>",
							        0 => "<span style=\"color: #FF0000;font-weight:bold;\">NO</span>"
							    );
							    $query = "SELECT pa_options.* FROM pa_options, primary_analysis where options_id = pa_options.id AND primary_analysis.id = " . $row ["id"] . "";
							    $res = mysqli_query($con, $query);
							    while ($row_details = mysqli_fetch_assoc($res)) {
							        ?>
							    							<table>
							    								<tbody>
							    									<tr>
							    										<th>remove bad reads</th>
							    										<td><?php echo $convArr[$row_details["rm_bad_reads"]]; ?></td>
							    									</tr>
							    									<tr>
							    										<th>trimming</th>
							    										<td><?php echo $convArr[$row_details["trimming"]]; ?></td>
							    									</tr>
							    									<tr>
							    										<th>masking</th>
							    										<td><?php echo $convArr[$row_details["masking"]]; ?></td>
							    									</tr>
							    									<tr>
							    										<th>alignment</th>
							    										<td><?php echo $convArr[$row_details["alignment"]]; ?></td>
							    									</tr>
							    									<tr>
							    										<th>aln_program</th>
							    										<td><?php echo $row_details["aln_prog"]; ?></td>
							    									</tr>
							    									<tr>
							    										<th>aln_options</th>
							    										<td><?php echo $row_details["aln_options"]; ?></td>
							   										</tr>
							    									<tr>
							    										<th>paired</th>
							    										<td><?php echo $convArr[$row_details["paired"]]; ?></td>
							    									</tr>
							    									<tr>
							    										<th>remove duplicates</th>
							    										<td><?php echo $convArr[$row_details["rm_duplicates"]]; ?></td>
							    									</tr>
							    								</tbody>
							    							</table><?php
							                }
							} 
							
							?></div><?php if ($row["level"] == 'primary' && $row["status"] == "completed") { ?>
							<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/QC/<?php  echo $row ["id"]; ?>_fastqc/fastqc_report.html" target="_blank"><i class="fa fa-folder"></i></a>
							<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/QC/<?php  echo $row ["id"]; ?>_fastqc.zip" target="_blank"><i class="fa fa-download"></i></a>
							<?php } else {?>
								<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/secondary/<?php  echo $row ["id"]; ?>/" target="_blank"><i class="fa fa-folder"></i></a>							
							</td>
							<?php  } ?>
					<td><?php echo $row["level"]; ?></td>
					<td><?php echo $row["method"]; ?></td>
					<td><?php echo $row["dateStart"];  ?></td>
					<td><?php if ($row["dateEnd"] != "") { echo $row["dateEnd"]; } else {echo "-"; };  echo " / " . $row['time'];  ?></td>
					
					<td><?php echo $row["status"]; ?>
					<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/users/<?php 
					   echo $row["user_name"];  
					   if ($row["level"] == 'primary') {
					       echo "/P";
					   } else if ($row["level"] == 'secondary') {
					       echo "/S";
					   } else if ($row["level"] == 'merging') {
					       echo "/M"; 
					   }else  {
					       echo "/O"; 
					   }
					   echo $row ["id"];
				    ?>" target="_blank"><i class="fa fa-newspaper-o"></i></a></td>
				    
					<?php if ($_SESSION['grantedAdmin'] == 1 ) {?><td><?php echo $row['user_name']; ?></td><?php  } ?>
					
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
				?></tbody>
		</table>
	</div>
</div>
