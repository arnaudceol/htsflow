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


if (isset($mergedPrimaryId)) {
    $queryPrimaryId =  $mergedPrimaryId;    
} else {
    // Get from sample id
    $sqlPrimary = "SELECT id FROM primary_analysis WHERE sample_id = '" . $sampleId . "'";
    $resultMerge = mysqli_query($con, $sqlPrimary);
    $count = $resultMerge->num_rows;
    
    if ($count > 0) {
        $queryPrimaryId = mysqli_fetch_assoc($resultMerge)['id'];
    }
    $resultMerge->close();
    mysqli_free_result($resultMerge);
    
}

if (isset($queryPrimaryId)) {
    $sqlMerge = "SELECT source_primary_id, sample_id, sample_name, raw_reads_num, reads_num, user_name FROM merged_primary, sample, primary_analysis, users WHERE sample_id = sample.id AND primary_analysis.id = source_primary_id AND users.user_id = sample.user_id AND result_primary_id = " . $queryPrimaryId;
    $resultMerge = mysqli_query($con, $sqlMerge);
    $count = $resultMerge->num_rows;
?><div class="table-container">
<?php
    $tableDiv = "tableSecondary";
    $phpTable = "secondary.php";
    
    ?><table class="mytable filterable" id="sf2" style="width: 100%;">
		<thead>
			<tr>
				<th class="centered">PRIMARY ID</th>
				<th class="centered">SAMPLE ID</th>
				<th style="text-align: left">Sample name</th>
				<th class="centered">READS NUM (raw/aligned)</th>
				<th class="centered">SAMPLE SUBMITTER</th>
			</tr>
		</thead>
		<tbody>
    <?php
    $numRighe = $result->num_rows;
    
    // if the number of rows returned from the DB is 0, we have no
    // results, so we print only dashes. Values otherwise.
    if ($numRighe != 0) {
        while ($rowMerge = mysqli_fetch_assoc($resultMerge)) {
            ?> <tr>
				<td class="centered"><?php   echo $rowMerge["source_primary_id"]; ?></td>
				<td class="centered"><?php echo $rowMerge["sample_id"]; ?></td>
				<td><?php echo $rowMerge["sample_name"]; ?></td>
				<td class="centered"><?php echo (isset($rowMerge["raw_reads_num"]) ? number_format($rowMerge["raw_reads_num"]) : " - ")  . " / " . (isset($rowMerge["reads_num"]) ? number_format($rowMerge["reads_num"]) : " - "); ?></td>
				<td class="centered"><?php echo $rowMerge["user_name"]; ?></td>
			</tr> <?php
        }
    }
    $resultMerge->close();
    mysqli_free_result($resultMerge);
    ?>
                </tbody>
	</table>
</div>

<?php } ?>