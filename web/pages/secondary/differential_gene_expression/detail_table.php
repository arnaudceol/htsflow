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

$querySpec = sprintf($querySpecPrepare, "differential_gene_expression", $row["id"]);
$resSpec = mysqli_query($con, $querySpec);
?><table>
<thead>
	<tr>
		<th>ID DEG</th>
		<th>EXP NAME</th>
		<th>CONDITION</th>
		<th>PRIMARY ID</th>
		<th>SAMPLE ID</th>
		<th>SAMPLE NAME</th>
		<th>SOURCE</th>
	</tr>
</thead>
<tbody><?php
while ($rowSpec = mysqli_fetch_assoc($resSpec)) {
    ?><tr>
		<td><?php echo $rowSpec["id"]; ?></td>
		<td><?php echo $rowSpec["exp_name"]; ?></td>
		<td><?php echo $rowSpec["cond"]; ?></td>
		<td><?php echo $rowSpec["primary_id"]; ?><a href="primary-browse.php?primaryId=<?php echo $rowSpec["primary_id"]; ?>"><i class="fa fa-reply"></i></a></td>
		<td><?php echo $rowSpec["sample_id"]; ?><a href="samples.php?sampleId=<?php echo $rowSpec["sample_id"]; ?>"><i class="fa fa-reply"></i></a></td>
		<td><?php echo $rowSpec["sample_name"]; ?></td>
		<td><?php echo $rowSpec["source_type"]; ?></td>
	</tr><?php
}
?></tbody>
</table>