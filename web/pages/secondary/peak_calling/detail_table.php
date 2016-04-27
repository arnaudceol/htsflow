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

$querySpec = "SELECT * FROM peak_calling WHERE secondary_id=" . $row["id"] . ";";
$resSpec = mysqli_query($con, $querySpec);

$firstRow= true;
$rowSpec = mysqli_fetch_assoc($resSpec)
?>



<ul>
<li><b>Type:</b> <?php echo  $rowSpec["program"]; ?></li>
<li><b>Stats:</b> <?php echo $rowSpec["stats"]; ?></li>
<li><b>P-value:</b> <?php echo $rowSpec["pvalue"]; ?></li>
<li><b>Saturation:</b> <?php echo $rowSpec["saturation"]; ?></li>
</ul>


<table>
<thead>
	<tr>
		<th>ID PEAK</th>
		<th>INPUT</th>
		<th>CHIP</th>
		<th>LABEL</th>
	</tr>
</thead>
<tbody>
<?php
    while ($rowSpec) {
        ?><tr>
		<td><?php echo $rowSpec["id"]; ?></td>
		<td><a href="primary-browse.php?primaryId=<?php echo $rowSpec["input_id"]; ?>"><?php echo $rowSpec["input_id"]; ?></a></td>
		<td><a href="primary-browse.php?primaryId=<?php echo $rowSpec["primary_id"]; ?>"><?php echo $rowSpec["primary_id"]; ?></a></td>
		<td><?php echo $rowSpec["label"]; ?></td>
	</tr><?php
		$rowSpec = mysqli_fetch_assoc($resSpec);
    }
?></tbody>
</table>