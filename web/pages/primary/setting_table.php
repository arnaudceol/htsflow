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

$tophat_options = "-r 170 -p 8 --no-novel-juncs --no-novel-indels --library-type fr-unstranded";
$bwa_options = "-t 16";
$bismarck_options = "-q --phred33-quals --non_directional";

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


if ($numGenomes > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different genomes. </div>"; 
}

if ($numMethods > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different sequencing methods. </div>";
}

if ($errors) {
    echo "<div>Please go back and select different genomes</div>"; 
} else {

$method = $methods[0];
	
?>
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
										<option value="FALSE">FALSE</option></select></td>
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
								<td>Program</td>
								<td><select name="aln_prog"
									onchange="if (this.options[1].selected) {
                                                    document.getElementById('aln_options').value = '<?php echo $bwa_options; ?>';
                                                } else {
                                                    document.getElementById('aln_options').value = '<?php echo $tophat_options; ?>';
                                                }">
                                                    <?php
                                                    
                                                    if ($method == "ChIP-Seq" || $method == "DNaseI-Seq") {
                                                        ?> <option
											value="bwa" selected>bwa</option><?php
                                                    } else 
                                                        if ($method == "BS-Seq") {
                                                            ?> <option
											value="bismark" selected>bismark</option><?php
                                                        } else {
                                                            ?><option
											value="tophat" selected>tophat</option>
										<option value="bwa">bwa</option><?php
                                                        }
                                                    ?>
                                        </select></td>
							</tr><?php
    if ($method == "ChIP-Seq" || $method == "DNaseI-Seq") {
        ?><tr>
								<td>Alignment options</td>
								<td><input type="text" name="aln_options" id="aln_options"
									size="55" value="<?php echo $bwa_options; ?>" /></td>
							</tr><?php
    } else 
        if ($method == "BS-Seq") {
            ?><tr>
								<td>Alignment options</td>
								<td><input type="text" name="aln_options" id="aln_options"
									size="55" value="<?php echo $bismarck_options; ?>" /></td>
							</tr><?php
        } else {
            ?><tr>
								<td>Alignment options</td>
								<td><input type="text" name="aln_options" id="aln_options"
									size="55" value="<?php echo $tophat_options; ?>" /></td>

							</tr><?php
        }
    ?>
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
								<td><select name="removeTmpfqfiles"><option value="TRUE"
											selected>TRUE</option>
										<option value="FALSE">FALSE</option></select></td>
							</tr><?php
    if ($method == "DNaseI-Seq") {
        ?><tr>
								<td>Remove Duplicates</td>
								<td><select name="removeDuplicates"><option value="TRUE">TRUE</option>
										<option value="FALSE" selected>FALSE</option></select></td>
							</tr><?php
    } else {
        ?><tr>
								<td>Remove Duplicates</td>
								<td><select name="removeDuplicates"><option value="TRUE"
											selected>TRUE</option>
										<option value="FALSE">FALSE</option></select></td>
							</tr><?php
    }
    ?>
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