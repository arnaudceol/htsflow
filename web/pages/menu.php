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

?>
<div id="header">
<div class="title">
	<!-- <a href="index.php"><img src="images/logo.png"></a> -->

</div>
<?php
// //////////////////////////////////////////////////////////////
// LOADING THE PAGE BASED ON THE RESULT OF LOGIN FUNCTION ///////
// ///////////////////////////////////////////////////////////////
?>
<div  class="menu" id="linkmenu-wrapper" >
	<div style="float: left;font-size: -1; color: orange; margin-left:20px">test version 2.0</div>	
	<div id="linkmenu"  class="menu">
		<ul class="menu">
			<li><a href="index.php">home</a></li>
			<li><a href="samples.php">samples</a></li>
			<li><a href="primary-browse.php">primary</a></li>
			<li><a href="secondary-browse.php">secondary</a></li>
			<!-- <li><a href="analyses.php">My flow</a></li>  -->
			<li><a href="usage.php">help</a></li>
			<li><a href="users.php">admin</a></li>
			<!-- <li><a href="#">contact</a></li> -->
		</ul>
	</div>
</div>

<?php 
if (isset($_SESSION["hf_user_name"])) {
    ?>
<div id="userlog" align="right">
	<div style="margin-right: 15px;">
		You are logged as <b><?php echo $_SESSION["hf_user_name"]; ?></b>. - <a
			href="index.php?logout">Logout</a>
	</div>
</div>
<?php
} else {
    ?>
<div id="userlog" align="right" style="background-color: #EFF8FB">
	<div style="margin-right: 15px;">You are not
		<a href="login.php">logged in</a>.</div>
</div>
<?php
}

?>
</div>