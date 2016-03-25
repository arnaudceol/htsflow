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

$querySpec = sprintf($querySpecPrepare, "inspect", $row["id"]);

$resSpec = mysqli_query($con, $querySpec);

?><table>
<thead>
	<tr>
		<th>ID </th>
		<th>type</th>
		<th>4SU ID</th>
		<th>RNA total ID</th>
		<th>Condition/Timepoint</th>
		<th>Labelling time</th>
		<th>deg during pulse</th>
		<th>modeling rates</th>
		<th>counts filtering</th>
	</tr>
</thead>
<tbody><?php
while ($rowSpec = mysqli_fetch_assoc($resSpec)) {
    ?><tr>
		<td><?php echo $rowSpec["id"]; ?></td>
		<td><?php echo $rowSpec["type"]; ?></td>
		<td><a href="primary-browse.php?primaryId=<?php echo $rowSpec["primary_id"]; ?>"><?php echo $rowSpec["primary_id"]; ?></a></td>
		<td><a href="primary-browse.php?primaryId=<?php echo $rowSpec["rnatotal_id"]; ?>"><?php echo $rowSpec["rnatotal_id"]; ?></a></td>
		<td><?php echo $rowSpec["cond"];  echo $rowSpec["timepoint"]; ?></td>
		<td><?php echo $rowSpec["labeling_time"]; ?></td>
		<td><?php echo $rowSpec["deg_during_pulse"]; ?></td>
		<td><?php echo $rowSpec["modeling_rates"]; ?></td>
		<td><?php echo $rowSpec["counts_filtering"]; ?></td>
	</tr><?php
}
?></tbody>
</table>
