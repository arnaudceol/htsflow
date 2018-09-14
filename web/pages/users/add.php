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

require_once ("config.php");
require ('pages/dbaccess.php');

if (isset ( $_POST ['addUserName'] )) {
	// add user
	$userName = $_POST ['addUserName'];
	$userSystemId = $_POST ['addUserSystemId'];
	
	$checkQuery = "SELECT * FROM users WHERE user_name = \"" . $userName . "\";";
	
	$res = mysqli_query ( $con, $checkQuery );
	$line = mysqli_fetch_assoc ( $res );
	
	if (! is_null ( $line )) {
		echo "use " . $userName . " already exist.";
	} else {

	    // Hash the password with the salt
	    //$hash = password_hash($userPassword, PASSWORD_BCRYPT );
		
		//$sql = "INSERT INTO users(user_name, password) VALUES ('" . $userName . "', '" . $hash . "')";
	    $sql = "INSERT INTO users(user_name, user_id) VALUES ('" . $userName . "', '" . $userSystemId . "')";
	    
		if (mysqli_query ( $con, $sql )) {
			echo "User " . $userName . " has been added";
		} else {
			echo "Failed to add user " . $userName;
		}
	}
} elseif (isset ( $_POST ['updatePassword'] )) {
	
    // Hash the password with the salt
    $hash = password_hash($userPassword, PASSWORD_BCRYPT );
    	
	$sql = "UPDATE users SET password = '" . $hash . " WHERE username = '" . $userName . "'";
	
	if (mysqli_query ( $con, $sql )) {
		echo "Password for user " . $userName . " has been updated.";
	} 
}
