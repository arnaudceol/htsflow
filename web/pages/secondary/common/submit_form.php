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

require_once ("../../../config.php");
require ('../../dbaccess.php');

$type = $_POST['type'];

$selectedSamples = array();
$values = explode(" ", $_POST['selectedIds']);
foreach ($values as $selectedId) {
    $value = preg_replace("/[\'\ ]/", "", $selectedId);
    if ($value != "") {
        array_push($selectedSamples, $value);
    }
}

global $con;

$sql = "SELECT DISTINCT ref_genome FROM sample, primary_analysis WHERE  sample_id = sample.id AND primary_analysis.id in (" . implode(", ", $selectedSamples) . ")";

$result = mysqli_query($con, $sql);

$genomes = array();

while ($row = mysqli_fetch_assoc($result)) {
    if (! in_array($row['ref_genome'], $genomes)) {
        array_push($genomes, $row['ref_genome']);
    }
}

$errors = FALSE;

$numGenomes = sizeof($genomes);

if ($numGenomes > 1) {
    $errors = TRUE;
    echo "<div><i style=\"color: red\" class=\"fa fa-exclamation-triangle\"></i>The samples selected are based on different genomes. </div>";
}

if ($errors) {
    echo "<div>Please go back and select different genomes</div>";
} else {
    ?>
    
<script type="text/javascript" src="pages/secondary/<?php echo $type;?>/submitFunctions.js"></script>
<div
	style="border: solid 1px #DDDDDD; margin-top: 15px; margin-bottom: 15px; padding: 5px; border-radius: 4px;">
	<table>
		<tbody>
			<tr>
				<td valign="middle"><p style="color: #00557F;">Title</td>
				<td valign="middle"><input type="text" id="title"> (use only
					alpha-numerical characters and _)</td>
			</tr>
			<tr>
				<td valign="middle"><p style="color: #00557F;">Description</td>
				<td valign="middle"><textarea id="description" name="description"
						cols="50" rows="4"></textarea></td>
			</tr>
		</tbody>
	</table>
</div>

<?php include '../'.$type.'/submit_form.php'?>

<div>
	<script>
// Function to check letters and numbers  
function titleCheck(title)  
{  
 var letterNumber = /^[0-9a-zA-Z_]+$/;  
 if(title.match(letterNumber))   
  {  
   return true;  
  }  
else  
  {    
   return false;   
  }  
}  
</script>
	<button type="button" name="" id="SUBMIT"
		onclick="if (titleCheck($('#title').val())) { submitSecondary() } else { alert('title cannot be empty and may contain only alpha-numerical characters and _'); return false;}">SUBMIT</button>
</div>
<?php
}
?>
