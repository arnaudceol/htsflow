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

require_once ("config.php");
require ('pages/dbaccess.php');

$require_permission= "browse";
include 'pages/check_login.php';


header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=file.csv");
header("Pragma: no-cache");
header("Expires: 0");

global $con;

$query = "SELECT secondary_analysis.id as secondary_id, method, secondary_analysis.title, secondary_analysis.status, secondary.label, 
    primary_analysis.id as primary_id, sample_id, sample_name, reads_num, raw_data_path    
    FROM sample, primary_analysis, secondary_analysis, users, 
    (select secondary_id, primary_id , 'base' as label FROM peak_calling UNION
    select secondary_id, input_id, 'input' FROM peak_calling UNION
    select secondary_id, primary_id, cond FROM differential_gene_expression UNION
    select secondary_id, primary_id, 'base' FROM methylation_calling UNION
    select secondary_id, primary_id, 'base' FROM expression_quantification UNION
    select footprint_analysis.secondary_id, primary_id, 'peak' FROM footprint_analysis, peak_calling WHERE peak_id = peak_calling.id) as secondary
    WHERE sample.id = sample_id AND primary_analysis.id = secondary.primary_id AND secondary.secondary_id = secondary_analysis.id 
    AND secondary_analysis.user_id = users.user_id AND user_name = '" . $_SESSION["hf_user_name"] . "'";

$result = mysqli_query($con, $query);

$fp = fopen('php://output', 'w');

fputcsv($fp, explode("\t", "Secondary ID\tAnalysis type\tTitle\tStatus\tLabel\tPrimary ID\tSample ID\tSample Name\tNumer of reads\tRaw Data Path"));

while ($row =  mysqli_fetch_assoc($result)) {    
    fputcsv($fp, $row);
}
mysqli_free_result($result);
fclose($fp);
