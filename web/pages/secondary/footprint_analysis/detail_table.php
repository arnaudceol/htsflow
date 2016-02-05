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

$querySpec = "SELECT peak_calling.secondary_id as peak_secondary_id, peak_id, primary_id, sample_id, footprint_analysis.exp_name, caller, footprint_analysis.pvalue
		FROM footprint_analysis, peak_calling, primary_analysis, sample 
		WHERE sample.id=sample_id AND primary_analysis.id = peak_calling.primary_id AND peak_calling.id = peak_id 
		AND footprint_analysis.secondary_id = " . $row["id"] . ";";

$resSpec = mysqli_query($con, $querySpec);

?><table><thead>
	<tr>
		<th>PEAK SECONDARY ID</th>
		<th>PEAK ID</th>
		<th>PRIMARY ID</th>
		<th>SAMPLE ID</th>
		<th>EXP NAME</th>
		<th>CALLER</th>
		<th>PVALUE</th>
	</tr>
</thead>
<tbody>
<?php
    while ($rowSpec = mysqli_fetch_assoc($resSpec)) {
        ?><tr>
		<td class="centered"><a href="secondary-browse.php?secondaryId=<?php echo $rowSpec["peak_secondary_id"]; ?>"><?php echo $rowSpec["peak_secondary_id"]; ?></a></td>
		<td class="centered"><?php echo $rowSpec["peak_id"]; ?></td>
		<td class="centered"><a href="primary-browse.php?primaryId=<?php echo $rowSpec["primary_id"]; ?>"><?php echo $rowSpec["primary_id"]; ?></a></td>
		<td class="centered"><a href="samples.php?sampleId=<?php echo $rowSpec["sample_id"]; ?>"><?php echo $rowSpec["sample_id"]; ?></a></td>
		<td class="centered"><?php echo $rowSpec["exp_name"]; ?></td>
		<td class="centered"><?php echo $rowSpec["caller"]; ?></td>
		<td class="centered"><?php echo $rowSpec["pvalue"]; ?></td>
	</tr><?php
    }
    ?></tbody>
    </table>