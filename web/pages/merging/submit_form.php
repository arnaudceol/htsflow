<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
?>
<script type="text/javascript" src="pages/merging/merging.js"></script>
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

$sql = "SELECT DISTINCT genome, seq_method, stranded FROM sample, primary_analysis, pa_options WHERE pa_options.id = options_id AND sample_id = sample.id AND primary_analysis.id in (" . implode(", ", $selectedSamples) . ")";

$result = mysqli_query($con, $sql);

$methods = array();
$genomes = array();
$stranded = array();

while ($row = mysqli_fetch_assoc($result)) {
    if (! in_array($row['genome'], $genomes)) {
        array_push($genomes, $row['genome']);
    }
    
    if (! in_array($row['seq_method'], $methods)) {
        array_push($methods, $row['seq_method']);
    }
    
    if (! in_array($row['stranded'], $stranded)) {
        array_push($stranded, $row['stranded']);
    }
}
mysqli_free_result($result);
$errors = FALSE;

$numMethods = sizeof($methods);
$numGenomes = sizeof($genomes);
$numStranded = sizeof($stranded);

if ($numGenomes > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different genomes. </div>";
}

if ($numMethods > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different sequencing methods. </div>";
}

if ($numStranded > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>All alignments must be either stranded or unstranded, but not a combination of both. </div>";
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
	<table style="text-align: left">
		<tr>
			<th>Sample name:</th>
			<td><input type="text" id="merge_name" /></td>
		</tr>
		<tr>
			<th>Description:</th>
			<td><textarea id="description" name="description" cols="50" rows="4"></textarea></td>
		</tr>
		<tr>
			<th>Remove duplicates:</th>
			<td><select id="rm_duplicates">
					<option value="1">TRUE</option>
					<option value="0">FALSE</option>
			</select></td>
		</tr>
		<?php if ($methods[0] == 'rna-seq') { ?>
		<tr>
			<th>Stranded (RNA-seq only)</th>
			<td><?php if ($stranded[0]) { echo "True"; } else { echo "False"; } ?></td>
		</tr>		
		<?php  } ?>
		<tr>
			<td><input type="hidden" name="genome" id="genome"
				value="<?php echo $genomes[0]; ?>" /> <input type="hidden"
				name="stranded" id="stranded" value="<?php echo $stranded[0]; ?>" />
				<button type="button" name="" id="SUBMIT" onclick="DNDmerging()">Merge</button></td>
		</tr>
	</table>


</div>

<?php } ?>
