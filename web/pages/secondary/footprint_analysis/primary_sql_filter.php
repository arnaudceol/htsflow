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

$primarySqlFilter =  "SELECT tc.user_id as user_id_fk, user_name, paired, tc.id as id_pre, sample_id as id_sample_fk, SOURCE,
genome, seq_method, pk.input_id as S2, pk.label, pk.id as id_peak, s.method, s.description, s.id as id_sec 
FROM primary_analysis tc, secondary_analysis s, peak_calling pk, users, pa_options  
WHERE (seq_method='DNaseI-Seq' OR seq_method='ChIP-seq') and s.id=pk.secondary_id and pk.input_id=tc.id AND users.user_id = tc.user_id AND pa_options.id  = options_id";

