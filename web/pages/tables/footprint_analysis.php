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

if (isset($_POST['user_id']) && $_POST['user_id'] != "") {
    $userIdSql = " users.user_id =" . $_POST['user_id'] . "";
    array_push($concatArray, $userIdSql);
}

if (isset($_POST['secondaryId']) && $_POST['secondaryId'] != "") {
    // Search peaks
    $secondaryIdSql = " pk.secondary_id =" . $_POST['secondaryId'] . "";
    array_push($concatArray, $secondaryIdSql);
}


if (isset($_POST['primaryId']) && $_POST['primaryId'] != "") {
    // Search peaks
    $primaryIdSql = " (pk.primary_id =" . $_POST['primaryId'] . " OR pk.input_id = " . $_POST['primaryId'] . ") ";
    array_push($concatArray, $primaryIdSql);
}

if (isset($_POST['sampleId']) && $_POST['sampleId'] != "") {
    // Search peaks
    $sampleIdSql = " sample.id  = '" . $_POST['sampleId'] . "' ";
    array_push($concatArray, $sampleIdSql);
}


if (isset($_POST['status']) && $_POST['status'] != "") {
	if ($_POST['status'] ==  "completed") {
		$status="s.status='completed'";
		array_push($concatArray, $status);
	} elseif ($_POST['status'] ==  "running") {
		$status="(s.status!='completed' AND s.status NOT like 'Error%')";
		array_push($concatArray, $status);
	} else { // error
		$status="s.status like 'Error%'";
		array_push($concatArray, $status);
	}
}



$sql = "SELECT tc.user_id, user_name, paired, tc.id as primary_id, sample.id as  sample_id, SOURCE, ref_genome, 
    seq_method, pk.id as peak_id, pk.label, s.method, s.description, s.id as secondary_id  
    FROM primary_analysis tc, secondary_analysis s, peak_calling pk, users, pa_options, sample
    WHERE (seq_method = 'DNaseI-Seq' OR seq_method='ChIP-seq') and s.id = pk.secondary_id and pk.primary_id = tc.id 
    AND users.user_id = tc.user_id AND options_id  = pa_options.id AND sample_id = sample.id ";

if (isset($selectedSamples) && sizeof($selectedSamples) > 0) {
	$tmpSTR = "";
	for($i = 0; $i < sizeof ( $selectedSamples ); $i ++) {
		if ($i == 0) {
			$tmpSTR .= " and (pk.id='" . $selectedSamples [$i] . "'";
		}
		if ($i != 0) {
			$tmpSTR .= " or pk.id='" . $selectedSamples [$i] . "'";
		}
	}
	$tmpSTR .= ")";
	$sql = $sql . $tmpSTR;
}

$filtArray = array_filter($concatArray);
$numOfelements = count($filtArray);
global $con;
switch ($numOfelements) {
    case 0:
        break;
    default:
        $sql = $sql . " AND " . implode(" AND ", $filtArray);
        break;
}


error_log($sql);
$result = mysqli_query($con, "SELECT COUNT(*) FROM (" . $sql . ") as g");
$count = $result->fetch_row();
$result->close();
$numRighe = $count[0];
$result = mysqli_query($con, $sql .  " ORDER BY s.dateStart desc" . $pagination );

?>
<div class="datagrid">
	<div class="table-container">
	<?php
$tableDiv = "tablePrimary";
$phpTable = "footprint_analysis.php";
include 'browseScripts.php';
?>
		<table class="mytable filterable" id="sf">

			<thead>
				<tr>
					<?php if ($selectable) { ?>
					<th>SELECT</th>
					<?php } ?>
					<th>SECONDARY ID</th>
					<th>ID PEAK</th>
					<th>LABEL</th>
					<th>METHOD</th>
					<th>INPUT</th>
					<th>CHIP</th>
					<th>EXP NAME</th>
					<th>METHOD</th>
					<th style="text-align: left">DESCRIPTION</th>
					<th>USER</th>
				</tr>
			</thead>
<?php
// if the number of rows returned from the DB is 0, we have no results, so we print only dashes. Values otherwise.

while ($line = mysqli_fetch_assoc($result)) {
    ?>
			<tr>
				<?php if ($selectable) { ?>
				<td class="centered"><input type="checkbox"
					name="selected_<?php echo $line["peak_id"]; ?>"  id="selected_<?php echo $row["peak_id"]; ?>"
					value="<?php echo $line["peak_id"]; ?>" 
					onclick="updateSelectedIds($(this).is(':checked'), '<?php echo $line["peak_id"]; ?>', 'selectedIds')" 
					<?php if (strpos($selectedIds,"'".$line["peak_id"]."'") !== false ) { echo "checked"; } ?>
					/></td>
				<?php } ?>
				<td class="centered"><?php echo $line["secondary_id"]; ?></td>
				<td class="centered"><?php echo $line["peak_id"]; ?></td>
				<td><?php echo $line["label"]; ?></td>
				<td class="centered"><?php echo $line["method"]; ?></td>
				<?php
    if ($line["method"] == "peak_calling") { 
        $querySpec = "SELECT * FROM peak_calling WHERE id=" . $line["peak_id"];
        $resSpec = mysqli_query($con, $querySpec);
        $lineSpec = mysqli_fetch_assoc($resSpec);
        ?>
				<td class="centered"><?php echo $lineSpec["primary_id"]; ?></td>
				<td class="centered"><?php echo $lineSpec["input_id"]; ?></td>
				<td class="centered"><?php echo $lineSpec["exp_name"]; ?></td>
				<td class="centered"><?php echo $lineSpec["program"]; ?></td>									
		<?php
    } else { 
        ?><td></td>
				<td></td>
				<td></td>
				<td></td>
					<?php } ?>
				<td><?php echo $line["description"]; ?></td>
				<td class="centered"><?php echo $line["user_name"]; ?></td>
			</tr>  
<?php
}

$result->close();

?>
		</tbody>
		</table>
	</div>
</div>