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
$selectedIds = $_POST["selectedIds"];

require_once ("../../config.php");
require ('../dbaccess.php');

$selectedSamples = array();
$values = explode(" ", $selectedIds);
foreach ($values as $selectedId) {
    $value = preg_replace("/[\'\ ]/", "", $selectedId);
    if ($value != "") {
        array_push($selectedSamples, $value);
    }
}

// Check number of methods/genomes
global $con;

$sql = "SELECT DISTINCT ref_genome, seq_method, reads_mode FROM sample WHERE  id in ('" . implode("', '", $selectedSamples). "')";

$result = mysqli_query($con, $sql);

$methods = array();
$genomes = array();

$pair = FALSE;

while ($row = mysqli_fetch_assoc($result)) {
    if (! in_array($row['ref_genome'], $genomes)) {
        array_push($genomes, $row['ref_genome']);
    }

    if (! in_array($row['seq_method'], $methods)) {
        array_push($methods, $row['seq_method']);
    }    
    
    if ($row["reads_mode"] == "PE") {
    	$pair = TRUE;
    } 
    
    
}

$errors = FALSE;

$numMethods = sizeof($methods);
$numGenomes = sizeof($genomes);

// Allow different genomes: we choose the one on which to do the alignment
if ($numGenomes > 1) {
    //$errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>Warning: the samples selected are based on different genomes. </div>"; 
}

if ($numMethods > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different sequencing methods. </div>";
}

if ($errors) {
    echo "<div>Please go back and select different genomes</div>"; 
} else {

$method = $methods[0];
	

$defaultOptions = parse_ini_file("../../defaults/primary_default.ini", true);

if (array_key_exists( $method,  $defaultOptions)) {
	$typeOfDefault = $method;
} else {
	error_log("Method " . $method . " is not configure in config file, use defaults.");
	$typeOfDefault = "default";
}

$programs = array();

foreach ($defaultOptions['options'] as $key => $value) {
	$programs[] = $key; 
}
// Get available genomes:
$availableAssemblies= array();
// BS need a different assembly
$availableAssembliesBs= array();

foreach (scandir(GENOMES_FOLDER) as $assembly) {
	if ($assembly[0] != "." && $assembly != 'alternative-genomes' ) {
		if (strrpos($assembly, "_bs") > 0) {
			$assemblyName = explode("_", $assembly) [0];
			array_push($availableAssembliesBs, $assemblyName );
		} else {
			error_log("assembly : " . $assembly);
			array_push($availableAssemblies, $assembly);
		}
	}
}



$defaultProgram = $defaultOptions[$typeOfDefault]["program"];
?>

<script>
								function updateOptions() {
									program = document.getElementById('aln_prog').value;
									options = "";
									<?php 
									foreach ($programs as $program) {
										?>if (program == '<?php echo $program;?>') {
											options = '<?php echo $defaultOptions['options'][$program];?>';
										}
										<?php 	
									}
									?>
									document.getElementById('aln_options').value = options;
								}

								function updateStranded() {
									program = document.getElementById('aln_prog').value;
									stranded = document.getElementById('stranded').value;
									options = "";

									if (program == 'tophat' && stranded == 'TRUE') {
										options = '<?php echo $defaultOptions['options']['tophat-stranded'];?>';
									} else {
										options = '<?php echo $defaultOptions['options']['tophat'];?>';
									}
									document.getElementById('aln_options').value = options;
								}
								
								</script>
								
<table>
	<tr>
		<th colspan="2" align="center">SETTINGS</th>
		<th></th>
	</tr>

	<tr>
		<td><table>
				<tr>
					<th align="left">Pre-process</th>
					<!--                                // there is an nested table for pre-process options-->
					<td><table>
							<tr>
								<td>Remove Bad Reads</td>
								<td><select name="remove_bad_reads"><option value="TRUE"
											selected>TRUE</option>
										<option value="FALSE">FALSE</option></select> <span style="color: red">Do not use for external data (only 
										for data  imported from the LIMS)</span></td>
							</tr>
							<tr>
								<td>Trimming</td>
								<td><select name="trimming"><option value="TRUE">TRUE</option>
										<option value="FALSE" selected>FALSE</option></select></td>
							</tr>
							<tr>
								<td>Masking</td>
								<td><select name="masking"><option value="TRUE" selected>TRUE</option>
										<option value="FALSE">FALSE</option></select></td>
							</tr>
						</table></td>

				</tr>
				<tr>
					<th align="left">Alignment</th>
					<td><table>
							<!--                                // there is an nested table for alignment method and options-->
							<tr>
								<td>Alignment</td>
								<td><select name="aln"><option value="TRUE" selected>TRUE</option>
										<option value="FALSE">FALSE</option></select></td>
							</tr>
							<tr>
								<td>Genome</td>
								<td><select name="genome"><?php foreach ($availableAssemblies as $assembly) {
									?><option value="<?php echo $assembly; ?>" <?php  if ($genomes[0] == $assembly) {
                                                    		echo "selected";
                                                    	}?>><?php echo $assembly; ?></option>
									<?php }?></select></td>
							</tr>
							<tr>
								<td>Program</td>
								<td><select name="aln_prog" id="aln_prog"
									onchange="updateOptions()">
                                                    <?php
                                                    
                                                    foreach ($programs as $program) {
                                                    	$selected = "";
                                                    	if ($defaultProgram == $program) {
                                                    		$selected = "selected";
                                                    	}
                                                    	echo "<option value=\"" . $program ."\" " .$selected . ">". $program . "</option>\n";
                                                    }
                                                    ?>
                                        </select></td>
							</tr>
    						<tr>
								<td>Alignment options</td>
								<td><input type="text" name="aln_options" id="aln_options"
									size="55" value="<?php 
										echo $defaultOptions['options'][$defaultProgram]; 
									?>" /></td>
							</tr>
							<tr>
								<td>Stranded (RNA-seq only)</td>
								<td><select name="stranded" id="stranded" onchange="updateStranded()"><option value="FALSE"  selected>FALSE</option>
								<option value="TRUE" >TRUE</option></select>
										</td>
							</tr>
							
                                                                             </table></td>
				</tr>
				<tr>
					<th align="left">Options</th>
					<td><table> <?php
    if ($pair == TRUE) {
        ?><tr>
								<td>Paired</td>
								<td><select name="paired"><option value="TRUE" selected>TRUE</option>
										<option value="FALSE">FALSE</option></select></td>
							</tr><?php
    } else {
        ?><tr>
								<td>Paired</td>
								<td><select name="paired"><option value="FALSE" selected>FALSE</option></select></td>
							</tr><?php
    }
    ?><tr>
								<td>Remove Temp Files</td>
								<td><select name="rm_tmp_files"><option value="TRUE"
											selected>TRUE</option>
										<option value="FALSE">FALSE</option></select></td>
							</tr><?php
							$defaultRemoveDuplicates = $defaultOptions[$typeOfDefault]["remove-duplicates"];
   							?><tr>
								<td>Remove Duplicates</td>
								<td><select name="rm_duplicates">
								<option value="TRUE" <?php if ($defaultRemoveDuplicates == TRUE) { echo "selected"; } ?>>TRUE</option>
								<option value="FALSE" <?php if ($defaultRemoveDuplicates == FALSE) { echo "selected"; } ?>>FALSE</option></select></td>
							</tr>
                        </table></td>
			
			</table></td>
		<td>
			<table>

			<?php
$pair = TRUE;
$num = 0;
foreach ($selectedSamples as $elem) {
    ?>
	<tr>
					<th align="left"><input type="hidden"
						id="selectedId<?php echo $num; ?>"
						name="selectedId<?php echo $num; ?>" value="<?php echo $elem;?>" />Sample <?php echo $elem;?>, description:</th>
				</tr>
				<tr>
					<td><textarea id="description<?php echo $num; ?>"
							name="description<?php echo $num; ?>" cols="50" rows="4"></textarea>
					</td>
				</tr>
	<?php
    $num = $num + 1;
}
?>
</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit"
			value="SUBMIT PRIMARY ANALYSIS" name="RUNsubmit"
			style="font-size: 1.2em" /></td>
	</tr>
</table>
<!--				// APRI INPUT NAMESAMPLE-->
<input type="hidden" name="nameSample"
	value="<?php
$stringa = "";
foreach ($selectedSamples as $elem) {
    $stringa .= $elem . "|";
}
echo substr($stringa, 0, - 1);
?>" />
<input type="hidden" name="analysis" value="primary" />

<?php 
}
?>