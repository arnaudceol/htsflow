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
<script type="text/javascript" src="pages/downsample/downsample.js"></script>
<?php 

require_once ("../../config.php");
require ('../dbaccess.php');

$selectedSamples = array();
$values = explode(" ", $_POST['selectedIds']);
foreach ($values as $selectedId) {
    $value = preg_replace("/[\'\ ]/", "", $selectedId);
    if ($value != "") {
        array_push($selectedSamples, $value);
    }
}


global $con;

$sql = "SELECT DISTINCT sample_name, primary_analysis.id, ref_genome, seq_method, reads_num FROM sample, primary_analysis WHERE  sample_id = sample.id AND primary_analysis.id in (" . implode(", ", $selectedSamples). ")";

$result = mysqli_query($con, $sql);

$methods = array();
$genomes = array();

$numRows = 0;

$minNumReads = "";
$originalNumReads = "";

// The one with the biggest number of reads
$selectedPrimaryId = 0;
$basedOnPrimaryId = 0;
$selectedSampleName = "";


while ($row = mysqli_fetch_assoc($result)) {
	$numRows = $numRows+1;
	
	if ($minNumReads == "") {
		$selectedPrimaryId = $row['id'];
		$basedOnPrimaryId = $row['id'];
		$selectedSampleName = $row['sample_name'];
		$minNumReads = $row['reads_num'];
		$originalNumReads = $row['reads_num'];
	} else {
		if ($row['reads_num'] < $minNumReads) {
			$minNumReads = $row['reads_num'];
			$basedOnPrimaryId = $row['id'];
		} else {
			$selectedPrimaryId = $row['id'];
			$selectedSampleName = $row['sample_name'];
			$originalNumReads = $row['reads_num'];
		}
	}
		
    if (! in_array($row['ref_genome'], $genomes)) {
        array_push($genomes, $row['ref_genome']);
    }

    if (! in_array($row['seq_method'], $methods)) {
        array_push($methods, $row['seq_method']);
    }
}
mysqli_free_result($result);
$errors = FALSE;

$numMethods = sizeof($methods);
$numGenomes = sizeof($genomes);


if ($numGenomes > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different genomes. </div>";
}

if ($numRows > 2) {
	$errors = TRUE;
	echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>Select no more than two samples. If you select two samples, the one with the bigges number of reads will be downsampled
		based on the number of reads in the second one.</div>";
}

if ($numMethods > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different sequencing methods. </div>";
}
if ($errors) {
    echo "<div>Please go back and select different genomes</div>"; 
} else {
?>

<div id="INFO"></div>
<div>
	<p style="font-weight: normal;">
		For merging samples you have to provide a name for the new merged
		sample.<br /> A new sample ID will be associated to
	</p>
</div>
<div id="containerSAMPLENAME" style="float: left; padding-right: 10px;">
<input type="hidden" name="downsample_primaryId" id="downsample_primaryId" value="<?php echo $selectedPrimaryId; ?>"/>
<table style="text-align:left">
<tr><th>Sample name: </th><td><input type="text" id="downsample_name" value="<?php echo $selectedSampleName; ?>_down<?php echo $minNumReads; ?>"/></td></tr>
<tr><th>Description: </th><td><textarea id="downsample_description" name="downsample_description" cols="50" rows="4">Primary id <?php echo $selectedPrimaryId; ?> downsampled. <?php 
if ($numRows == 2) {
	echo " to the number of reads in primary " . $basedOnPrimaryId . " (" . 	$minNumReads . " reads).";
}
?></textarea></td></tr>
<tr><th>Target number of reads: </th><td><input type ="text" id="downsample_target_num_reads" name="downsample_target_num_reads" size"8" value="<?php echo $minNumReads; ?>"/>
<input type ="hidden" id="downsample_original_num_reads" name="downsample_original_num_reads" value="<?php echo $originalNumReads; ?>"/></td></tr>
<tr><th><td><button type="button" name="" id="SUBMIT" onclick="DNDdownsampling()">Downsample</button></td></tr>
	
</table>
	
	
</div>

<?php } ?>
