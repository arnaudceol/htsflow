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

// Originally, jobs to be submitted were written into a file.
// In current version we are updating a table in the database
function putInUserFile($jobs)
{
    global $con;
    $user_id = $_SESSION["hf_user_id"] ;
    
    foreach ($jobs as $command) {
        $type = explode("\t" , $command) [0];
        $id = explode("\t" , $command) [1];
        
        $sqlQuery = "insert into job_list (analyses_type , analyses_id , action , user_id ) values ('" . $type . "', ". $id . ", 'run', ". $user_id . ") ";
        $res = mysqli_query($con, $sqlQuery);        
    }
       
    return sizeOf($jobs);  
}
