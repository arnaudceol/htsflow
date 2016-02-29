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

// Required variable: primaryId
$secondaryToPrimarySql =  " SELECT peak_calling.primary_id FROM footprint_analysis, peak_calling WHERE peak_id = peak_calling.id AND footprint_analysis.secondary_id IN (" . $secondaryId . ")"
." UNION  SELECT peak_calling.input_id FROM footprint_analysis, peak_calling WHERE peak_id = peak_calling.id AND footprint_analysis.secondary_id IN (" . $secondaryId . ")"	;