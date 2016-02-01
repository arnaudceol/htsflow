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

// If no user is present in the database, we create automatically an administrator
// username: admin, password: htsflow
if (! isset($HTSFLOW_PATHS['LDAP_URL'])) {
$checkQuery = "SELECT * FROM users WHERE user_name = 'admin'"; //granted_admin = 0";

$res = mysqli_query($con, $checkQuery);
$line = mysqli_fetch_assoc($res);
if (is_null($line)) {
    
    $userName = "admin";
    $userPassword = "htsflow";
    
    // Hash the password with the salt
    $hash = password_hash($userPassword, PASSWORD_BCRYPT );
    
    $sql = "INSERT INTO users(user_name, password, granted_admin) VALUES ('" . $userName . "', '" . $hash . "', 1)";
    
    if (mysqli_query($con, $sql)) {
        echo "User '" . $userName . "' with password '" . $userPassword . "' has been created. Please log in.";
    } else {
        echo "Failed to add user " . $userName;
    }
}
}
?>
