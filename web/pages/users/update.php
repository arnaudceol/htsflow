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

require_once ("../../config.php");

require_once ('../dbaccess.php');

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <body>
<?php 
	if (isset($_POST['userName'])) {
		// add user
		$userName = $_POST['userName'];
		$sql = "INSERT INTO users(user_name) VALUES ('".$userName."')";
		
		if (mysqli_query($con, $sql )) {
			echo "User " . $userName . " has been added";
		} else {
			echo "Failed to add user " . $userName;
		}
	} else {
		$grant_browse = 0;
		$grant_primary = 0;
		$grant_secondary = 0;
		$grant_admin = 0;
		if (isset($_POST['grantBrowse'])) {
			$grant_browse = 1;
		}
		if (isset($_POST['grantPrimary'])) {
			$grant_primary = 1;
		}
		if (isset($_POST['grantSecondary'])) {
			$grant_secondary = 1;
		}
		if (isset($_POST['grantAdmin'])) {
			$grant_admin = 1;
		}
		$userId = $_POST['userId'];
		$sql = "UPDATE users SET granted_browse = '" . $grant_browse . "' , granted_primary = '" . $grant_primary
		. "', granted_secondary = '" . $grant_secondary . "', granted_admin = '" . $grant_admin . "' WHERE user_id = " . $userId;
		
		if (mysqli_query($con, $sql )) {
			echo "User " . $userId . " has new permission: grant_browse = " . $grant_browse . " , grant_primary = " . $grant_primary
		. ", grant_secondary = " . $grant_secondary . ", grant_admin = " . $grant_admin;
		} else {
			echo "Failed to update permissions for user " . $userId;
		}
		
	}

?>        
</body>
</html>