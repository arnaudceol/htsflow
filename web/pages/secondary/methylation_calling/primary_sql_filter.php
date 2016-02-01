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

$baseQuery = "SELECT dateStart,  dateEnd, CASE WHEN status = 'completed' THEN timediff( dateEnd, dateStart )  ELSE timediff( NOW(), dateStart ) END AS time, options_id, primary_analysis.id as id_pre, sample_id as id_sample_fk, reads_num, raw_reads_num, pa_options.*, status, ref_genome, user_name, seq_method, origin as SOURCE, sample_name, reads_mode, raw_data_path FROM primary_analysis, pa_options, users, sample WHERE sample.id = primary_analysis.sample_id AND pa_options.id = primary_analysis.options_id and primary_analysis.user_id = users.user_id " ;
$primarySqlFilter =  $baseQuery . " AND primary_analysis.status='completed' AND seq_method='BS-Seq'";
