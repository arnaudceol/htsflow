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
ini_set('display_errors', 'On');
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ALL);
// I don't know if you need to wrap the 1 inside of double quotes.
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);

require_once ("config.php");
require_once ('pages/dbaccess.php');
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
			<div>
				<b>HTS-flow</b> provides a framework for the management of
				NGS-samples, data retrieval, primary and secondary analyses.

			</div>



			<div class="apptile-container"
				style="width: 800px; margin: auto; padding-top: 55px; padding-bottom: 55px;">
				<a href="samples.php"><div class="apptile big">
						<div class="apptile-title blue">Samples</div>
						<div class="tiletext">
							List of samples from LIMS or external data. Click here to
							<ul>
								<li>Browse samples.</li>
								<li>Edit sample information (name, method, ref. genome).</li>
								<li>Add external data.</li>
							</ul>

						</div>
					</div></a> <a href="primary-browse.php"><div class="apptile big">
						<div class="apptile-title blue">Primary</div>
						<div class="tiletext">
							Platform-independent preprocessing of samples (quality control,
							masking and alignment to the reference genome). Click here to:
							<ul>
								<li>Browse running and completed primary analysis.</li>
								<li>Merge samples.</li>
								<li>Start new primary analyses.</li>
							</ul>
						</div>
						<br />
					</div></a> <a href="secondary-browse.php"><div class="apptile big">
						<div class="apptile-title blue">Secondary</div>
						<div class="tiletext">
							Higher level analyses platform and sample-dependent. Click here
							to browse or start secondary analysis:
							<ul>
							<?php
    foreach (scandir("pages/secondary/") as $type) {
        if ($type != "." && $type != ".." && $type != "common") {
            // Create a good looking title, with space and first letter uppercase
            $title = ucwords(str_replace("_", " ", $type));
            echo "<li>" . $title . "</li>";
        }
    }
    ?></ul>
						</div>
					</div></a>
			</div>


		</div>
<a href="http://127.0.0.1:7085/UnibrowControl?scriptfile=http://127.0.0.1:3030/htsflow2/web/igb.php?id%3D12980">IGB</a>
	</div>
        <?php
        include ("pages/footer.php");
        ?>

</body>
</html>