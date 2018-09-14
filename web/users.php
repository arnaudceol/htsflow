<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
session_start();

require_once ("config.php");

require_once ('pages/dbaccess.php');

$require_permission = "admin";
include 'pages/check_login.php';

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php include ("pages/header.php"); //header of the page ?>
    <body>
	<div id="wrapper">
        <?php
        include ("pages/menu.php"); // import of menu
        ?><div id="content">
        <?php
        include 'pages/users/add.php';
        ?>
        <div style="float: right;" class="filtertable"
				onclick="javascript:toggle('addUser')">Add user</div>
			<div style="clear: both;"></div>


			<div id="messages"></div>
     		<?php
    include 'pages/tables/users.php';
    ?>    
     		<div id="addUser" style="display: none" class="over-form">
				<form method="post">
					User name (e.g. cbello): <input type="text" id="addUserName" name="addUserName"></input>
<!-- 					Password: <input type="password" id="addUserPassword" -->
<!-- 						name="addUserPassword"></input>  -->
					User id (e.g. ieo1234): <input type="text" id="addUserSystemId"
                     	name="addUserSystemId">
					<input type="submit"
						value="add user" />
				</form>
			</div>
		</div>        
       
     </div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>