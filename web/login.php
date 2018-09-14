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
session_start ();

require_once ("config.php");

require_once ('pages/dbaccess.php');

header ( 'Content-type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php include ("pages/header.php"); //header of the page ?>
    <body>
	<div id="wrapper">
        <?php include ("pages/menu.php"); 
        ?><div id="content">
			<div>
				<div class="datagrid" style="width: 100%; margin: 0 auto;">
					<div style='background-color: #ffffff; padding: 50px;'>
						<p></p>
						<div><?php
// show potential errors / feedback (from login object)
if (isset ( $errors )) {
	echo $error;
}
if (isset( $messages)) {
	echo $message;
}


include 'pages/users/createAdmin.php';

?>
   	 <p>To access this page you need to login.</p>
							<!-- login form box -->
							<form method="post" action="index.php" name="loginform">
								<label for="login_input_username">Username</label> <input
									id="login_input_username" class="login_input" type="text"
									name="user_name" required /> <label for="login_input_password">Password</label>
								<input id="login_input_password" class="login_input"
									type="password" name="user_password" autocomplete="off"
									required /> 
								<select id="login_input_group" class="login_input"
									type="select" name="user_group" autocomplete="off"
									required>
									<option value="BA">BA</option>
 									<option value="PGP">PGP</option>
 									<option value="FN">FN</option>
 									<option value="FN">SC</option>
 								</select>
								<input type="submit" name="login" value="Log in" />
							</form>
						</div>
					</div>

				</div>
				<div style="clear: both; height: 10px;"></div>
			</div>
		</div>

     </div>
     <?php include ("pages/footer.php");    ?>

</body>
</html>