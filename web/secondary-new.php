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
ini_set('display_errors', 'On');
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ALL);
// I don't know if you need to wrap the 1 inside of double quotes.
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);

require_once ("config.php");
require ('pages/dbaccess.php');

$require_permission= "secondary";
include 'pages/check_login.php';

// Any selection done?
$selectedSamples = array();

foreach ($_POST as $key => $value) {
    // this is not the good solution, the field shoud have a different name
    if (preg_match("/^selected_/", $key)) {
        array_push($selectedSamples, $value);
    }
}

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
        <!-- Method selection -->
			<form name="CONTROL" action="secondary-new.php" method="post"
				style="margin-bottom: 20px">
				<b>Secondary analyses:</b> 
			<?php
    foreach (scandir("pages/secondary/") as $typeSecondary) {
        if ($typeSecondary != "." && $typeSecondary != ".."&& $typeSecondary != "common") {
        
            // Create a good looking title, with space and first letter uppercase
            $title = ucwords(str_replace("_", " ",$typeSecondary));
        
            $selected = false;
            if (isset($_POST['type']) && $_POST['type'] == $typeSecondary) {
                $selected = true;
            }
            
            ?><input onclick='this.form.submit()' type="radio" name="type"
					value="<?php echo $type; ?>"
					<?php if ($selected) { echo "checked"; }?> /><?php
            
            if ($selected) {
                echo "<b>" . $title . "</b>";
            } else {
                echo $title;
            }
        }
        ?><?php
    }
    ?>				
		</form>

<?php
    
    if (isset($_POST['type'])) {
        $type = $_POST['type'];
        ?>
        
        
	<script>
	function loadSubmitTable() {  
		if ($('#selectedIds').val().trim() === "") {
			alert("No row selected");
		} else {

    	$.post("pages/tables/<?php if ($type == "footprint_analysis") { echo "footprint_analysis";  } else { echo "primary"; }?>.php", {
        	selectable: "false", 
		    type: "completed",
			selectedIds: $('#selectedIds').val(),
            <?php
      		  foreach ($_POST as $key => $value) {
            	if ($key != "selectable") {
               		echo "$key: \"$value\",\n";
            	}
        	  }
        	?>	
		}, function(response) {
    		$( "#tableSubmit" ).html(response);
    		toggle('submit_form');
		});        
		
    	// load settings    	
        $.post("pages/secondary/common/submit_form.php", {
			selectedIds: $('#selectedIds').val(),
			type: "<?php echo $type;?>"
    	}, function(response) {
    			$( "#settingsSubmit" ).html(response);
    	});        
        }   
	}
			</script>

			<div style="display: inline-block;">
			<?php
        $tableDiv = "tablePrimary";
        $selectable = true;

        if (file_exists ( 'pages/secondary/' . $type . '/primary_table_filter.php' ) ) {
            include 'pages/secondary/' . $type . '/primary_table_filter.php';
        } else {
            // default primary table filter       
                include 'pages/filters/primary_table_filter.php';
        }
        ?>
			</div>
			<div style="padding-top: 20px; padding-bottom: 20px">Select one or more samples and press the Settings button: 
			<span style="width: 150px; text-align: center"
				class="filtertable"
				onclick="loadSubmitTable();">Settings</span>
				</div>
			<div style="clear: both;"></div>
			<form name="<?php echo $type; ?>" action="" method="post">
				<div id="tablePrimary"></div>
				<script>
                 $.post("pages/tables/<?php if ($type == 'footprint_analysis') { echo "footprint_analysis.php"; } else { echo "primary.php"; }?>", {
            			selectable: "true",
            			type: "<?php echo $type;?>",
                    	selectedIds: $('#selectedIds').val(),
                    	status : "completed"
					}, function(response) {
					    // Log the response to the console
	          		    //  console.log("Response: "+response);
					    $( "#tablePrimary" ).html(response);
					});              
           	 	</script>
			</form>


			<div id="submit_form" style="display: none" class="over-form">
				<div
					style="text-align: right; margin: 20px; font-style: italic; font-weight: bold"
					onclick="javascript:toggle('submit_form')">close</div>
				<div id="tableSubmit"></div>
				<div id="settingsSubmit"></div>
			</div>
<?php
    }

?>
</div>

     </div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>
