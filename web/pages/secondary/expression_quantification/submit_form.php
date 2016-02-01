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

?><div>
	For each sample, please fill
	<ul>
		<li>Primary ID: the ID of the primary analysis (check the order).</li>
		<li>MIX: the type of ERCC Mix used if available;</li>
	</ul>
</div>

<div id="container2">

	<?php
// SelectedIds and $values (the selected ids) has been initialized in tableCommons
$selectedIds = explode(" ", $_POST["selectedIds"]);
rsort($selectedIds);
for ($i = 0; $i < count($selectedIds); $i ++) {
    $id = preg_replace("/[\'\ ]/", "", $selectedIds[$i]);
    if ($id != "") {
        ?>
	<div>
		Primary ID: <input type="text" id="sample<?php echo $i; ?>"
			value="<?php echo $id; ?>" /> MIX: <select id="mix<?php echo $i; ?>">
			<option value=""></option>
			<option value="1">1</option>
			<option value="2">2</option>
		</select>
	</div>
	<?php
    }
}
?>

</div>

<div id="INFO"></div>
