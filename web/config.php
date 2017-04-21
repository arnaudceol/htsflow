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
// ini_set('display_errors', 'On');
// error_reporting(E_ALL ^ E_WARNING);
// ini_set("display_startup_errors", 1);
// ini_set("display_errors", 1);

$conf= "/home/aceol/Work/data/htsflow-conf/htsflow-" + $_SESSION["hf_user_group"]+" .ini";

if (! file_exists($filename)) {
	header('Location: missing_config.php');
}

$HTSFLOW_PATHS=array();

$conf = parse_ini_file($conf);

			
foreach ($conf as $key => $value) {			
			if (strpos($value, "]") != false) {
				$pattern = '/\[(.*)\].*/';
				preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE);				
				$pattern = '/\[.*\](.*)/';
				$replacement = $HTSFLOW_PATHS[$matches[1][0]] . '${1}';
				$value = preg_replace($pattern, $replacement, $value);
			}
			$HTSFLOW_PATHS[$key] = $value;
		}

define('SCRIPTS_FOLDER', $HTSFLOW_PATHS['HTSFLOW_SCRIPTS']);
define('TOOLS_FOLDER', $HTSFLOW_PATHS['HTSFLOW_TOOLS']);
define('USERS_FOLDER', $HTSFLOW_PATHS['HTSFLOW_USERS']);
define('DB_CONF', $HTSFLOW_PATHS['DB_CONF']);
define('R_BASE', $HTSFLOW_PATHS['Rbase'] );
define('GENOMES_FOLDER', $HTSFLOW_PATHS['HTSFLOW_GENOMES']);

// Use this to display a message on each page, e.g. for a test server
define('WARNING_MESSAGE', "TEST LOCAL DATABASE");




