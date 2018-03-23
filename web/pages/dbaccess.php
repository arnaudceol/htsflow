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

// /////////////////////////////////////////////////////////////
// Create connection to the database
// the config file is located in a directory that is not
// visible from the web, in order to protect the access to
// the database.

if ( DB_CONF != '' ) {
    $config = parse_ini_file(DB_CONF, true);
    $hostname = $config['database']['hostname'];
    $username = $config['database']['username'];
    $loginPassword = $config['database']['pass'];
    $dbname = $config['database']['dbname'];
    $con = mysqli_connect($hostname, $username, $loginPassword, $dbname);
    
    // /////////////////////////////////////////////////////////////
    // to use the connection to the db in each function use:
    // global $con;
    // ---- end of connection ---- //
    // /////////////////////////////////////////////////////////////
    
    $con->query("SET GLOBAL general_log = 'ON'");
}
// Get new id
if (! function_exists("getNewId")) {

    function getNewId()
    {
        global $con;
        
        $queryGetId = "SELECT nextSampleId();";
        
        $result = mysqli_query($con, $queryGetId);
        
        if ($result) {
            $id = mysqli_fetch_row($result)[0];
            return $id;
        }
        
        return NULL;
    }
}
  
