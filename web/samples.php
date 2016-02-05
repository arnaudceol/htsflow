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
require_once ("config.php");
require ('pages/dbaccess.php');


$require_permission= "browse";
include 'pages/check_login.php';

header('Content-type: text/html; charset=utf-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php
    include ("pages/header.php"); // header of the page
    include 'pages/sample/editFunctions.php';
    
    ?>
    <body>
		<script src="libs/jquery.fileTree-1.01/jqueryFileTree.js" type="text/javascript"></script>
		<link href="libs/jquery.fileTree-1.01/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
	
	<script>
	function goToPrimary() {                   
		if ($('#selectedIds').val().trim() == '')) {
			alert("Select at least one sample.");
		} else {	
			$('body').append($('<form/>')
					  .attr({'action': 'primary-browse.php', 'method': 'post', 'id': 'toPrimaryForm'})
					  .append($('<input/>')
					    .attr({'type': 'hidden', 'name': 'sampleId', 'value': $('#selectedIds').val().replace( /\'/g, '')  })
					  )
					).find('#toPrimaryForm').submit();
		 }  
	}

	function goToSecondary() {                   
		if ($('#selectedIds').val().trim() == '')) {
			alert("Select at least one sample.");
		} else {	
			$('body').append($('<form/>')
					  .attr({'action': 'secondary-browse.php', 'method': 'post', 'id': 'toSecondaryForm'})
					  .append($('<input/>')
					    .attr({'type': 'hidden', 'name': 'sampleId', 'value': $('#selectedIds').val().replace( /\'/g, '')  })
					  )
					).find('#toSecondaryForm').submit();
		 }  
	}


	</script>
	
	<div id="wrapper">
        <?php
        include ("pages/menu.php"); // import of menu
        ?><div id="content"><?php
                include ("pages/external/control_buttons.php");
                ?>
	   		        
           <div style="display: inline-block;">
	    	<?php
                $selectable = true;
                $editable = true;
                $tableDiv = "sampleTable";
                include 'pages/filters/sample_filter.php';
                ?>
	   		</div>
	   		<div class="filtertable" style="float: right; margin: 10px;"> 
			<a href="#"   class="fa fa-plus-square fa-2x" 
				onclick="javascript:toggle('external_div')" title="Add external data" ></a>
			<a href="#"   class="fa fa-reply fa-2x" 
				onclick="goToSecondary();return false;" title="Show secondary analysis for selected samples" ></a>
			<a href="#"   class="fa fa-share fa-2x" 
				onclick="goToPrimary();" title="Show primary analysis for selected samples" ></a>
			</div>
			
			<div style="clear: both;"></div>

			<div id="sampleTable"></div>
			<script>
			$.post("pages/tables/samples.php", {
				editable: true,
				selectable: true,
       			<?php if (isset ( $_POST ['seq_method'] )) { echo "seq_method: \"" . $_POST ['seq_method'] ."\",\n"; } ?>
       			<?php if (isset ( $_POST ['user_id'] )) { echo "user_id: \"" . $_POST ['user_id'] ."\",\n" ; } ?>
       			<?php if (isset ( $_POST ['ref_genome'] )) { echo "ref_genome: \"" . $_POST ['ref_genome'] ."\",\n"; } ?>
       			<?php if (isset ( $_POST ['source'] )) { echo "source: \"" . $_POST ['source'] ."\",\n"; } ?>
       			<?php if (isset ( $_POST ['selectedIds'] )) { echo "selectedIds: \"" . $_POST ['selectedIds'] ."\",\n"; } ?> 	
       			<?php if (isset ( $_REQUEST ['sampleId'] )) { echo "sampleId:  \"" . $_REQUEST['sampleId']."\",\n"; } ?>
       			<?php if (isset ( $_POST ['sampleName'] )) { echo "sampleName:  \"" . $_POST['sampleName']."\",\n"; } ?>  	
       			<?php if (isset ( $_POST ['primaryId'] )) { echo "primaryId:  \"" . $_POST['primaryId']."\",\n"; } ?>  	 						
			}, function(response) {
			    // Log the response to the console
      		    console.log("Response: <?php if (isset ( $_POST ['sampleId'] )) { echo $_POST['sampleId'];} ?>"+response);	          		   
			    $( "#sampleTable" ).html(response);
			}); 
            </script>

		</div>

		<script>
		function checkPaths() {	
				if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// 	code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
		
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {	
					document.getElementById(tableDiv).innerHTML = xmlhttp.responseText;
				}
			};

			$.post("pages/external/check_paths.php", {	
				paths: $("textarea#paths").val()
			}, function(response) {
			    // 	Log the response to the console
				console.log("Response: "+response);
		  
			    $( '#pathsContent' ).html(response);
			});
		}
		


</script>

		<div id="external_div" style="display: none" class="over-form">			
			<div
				style="text-align: right; margin: 20px; font-style: italic; font-weight: bold"
				onclick="javascript:toggle('external_div')">close</div>
			<div class="title">Add external data: (<a href="http://localhost:3030/usage.php#title4">see how to prepare data</a>).</div><br/>
			<form name="external" action="samples.php" method="post">				
				<table ><tr style="vertical-align: top;"><td>
				<table >
					<tbody>
						<tr>
							<th align="right">Sequencing method</th>
							<td><input type="hidden" name="submitform" value="external" /> <input
								type="radio" name="seq_method" value="RNA-Seq" />RNA-Seq
								&nbsp;&nbsp; <input type="radio" name="seq_method"
								value="ChIP-Seq" />ChIP-Seq &nbsp;&nbsp; <input type="radio"
								name="seq_method" value="DNaseI-Seq" />DNaseI-Seq &nbsp;&nbsp; <input
								type="radio" name="seq_method" value="BS-Seq" />BS-Seq
								&nbsp;&nbsp;</td>
						</tr>

						<tr>
							<th align="right">Reads Length</th>
							<td><input type="text" name="reads_length" size="5" value="0" /></td>
						</tr>

						<tr>
							<th align="right">Reads Type</th>
							<td><input type="radio" name="reads_mode" value="SR" />Single End
								&nbsp;&nbsp; <input type="radio" name="reads_mode" value="PE" />Pair
								End &nbsp;&nbsp;</td>
						</tr>

						<tr>
							<th align="right">Reference Genome</th>
							<td><input type="radio" name="ref_genome" value="mm9" />mm9
								&nbsp;&nbsp; <input type="radio" name="ref_genome" value="mm10" />mm10
								&nbsp;&nbsp; <input type="radio" name="ref_genome" value="hg18" />hg18
								&nbsp;&nbsp; <input type="radio" name="ref_genome" value="hg19" />hg19
								&nbsp;&nbsp; <input type="radio" name="ref_genome" value="rn5" />rn5
								&nbsp;&nbsp; <input type="radio" name="ref_genome" value="dm6" />dm6
								&nbsp;&nbsp;</td>
						</tr>
						<tr ><th align="right">Path(s)</th>						
							<td ><textarea name="paths" id="paths" rows="6" cols="50"></textarea><br/>
							<input type="button" value="check" name="check" onclick="checkPaths()"></input>
							<input type="submit"  value="SUBMIT" name="submitExt" />
							</td>
						</tr>
					</tbody>
				</table>
				</td>
				<td style="padding-right: 25px;"><div>Single click to browse, double click to add a file/folder:</div><div id="treepath">here</div></td>
				<td><div id="pathsContent" style="float: right; width: 550px"></div></td>
				</tr></table>
			</form>
		</div>
		
		<script type="text/javascript">
			
		$(document).ready( function() {
		    $('#treepath').fileTree({  script: 'libs/jquery.fileTree-1.01/connectors/jqueryFileTree.php', root: '<?php echo $HTSFLOW_PATHS['HTSFLOW_UPLOAD_DIR']; ?>', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) { 
					alert(file);
				});
		});	
		    
        </script>
    
     </div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>


