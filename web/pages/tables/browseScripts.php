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
?><!--  
 Generate the link to browse the table (e.g. next, previous, number to show)
 need as input the name of the offset, limit and name of the div to update (AJAX)
 @tablePhp: the PHP script that will write the table. It should be in the pages/table/directory
 @tableDiv: the div in which the table is written
 -->
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>

<script>
// Give a different name to the function each time this script is calle
function browse<?php echo$tableDiv; ?>(tablePhp, tableDiv, offset, limit) {	
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {	
				document.getElementById(tableDiv).innerHTML = xmlhttp.responseText;
			}
		};

	$.post("pages/tables/" + tablePhp, {
	<?php
    foreach ($_POST as $key => $value) {    
        if ($key != 'offset' && $key != 'limit') {
            echo "$key: \"$value\", // $value\n";
        }
    }
    ?>
		selectedIds: $("#selectedIds").val(),
		offset: offset,
		limit: limit,
	}, function(response) {
	    // Log the response to the console
		//console.log("Response: "+response);	 
		  
	    $( tableDiv ).html(response);
	});
}
		


</script>

<div style="margin: 10px; width:100%">

<script>

function refreshTable() {
	 $("#refreshinput").toggleClass('fa-spin');
	setTimeout(			
			  function() 
			  {
				 $("#refreshinput").toggleClass('fa-spin');

					browse<?php echo$tableDiv; ?>('<?php echo $phpTable; ?>', '#<?php echo $tableDiv; ?>', <?php echo $offset; ?>, <?php echo $limit; ?>)
			  }, 1000);
}

</script>
<?php include '../legends.php';?>
<span class="fa-stack " >
  <i class="fa  fa-circle-o fa-stack-2x" ></i>
  <a class="fa fa-refresh fa-stack-1x" id="refreshinput" onclick="refreshTable()" ></a> 
</span>
<?php
$numPages = floor($numRighe / $limit) +1;
$page = $offset / $limit + 1;

if ($numPages > 1) {
?>
 page <?php echo $page . " / " . $numPages; ?> (<?php echo $numRighe; ?> results)
 <?php } else {
     echo $numRighe; ?> results 
 <?php  } ?>
 
<?php 
if ($page > 1) {
    ?><a class="fa fa-fast-backward" style="text-decoration: none; padding: 5px" href="#"
    	onclick="browse<?php echo $tableDiv; ?>('<?php echo $phpTable; ?>', '#<?php echo $tableDiv; ?>', <?php echo 0; ?>, <?php echo $limit; ?>)"></a><?php      
} else {
    ?><i style="color: #BBBBBB; padding: 5px" class="fa fa-fast-backward"></i><?php 
}

if ($page > 1) {
    ?><a class="fa fa-backward"  style="text-decoration: none; padding: 5px" href="#"
	onclick="browse<?php echo $tableDiv; ?>('<?php echo $phpTable; ?>', '#<?php echo $tableDiv; ?>', <?php echo $offset - $limit; ?>, <?php echo $limit; ?>)"></a><?php  
} else {
    ?><i style="color: #BBBBBB; padding: 5px" class="fa fa-fast-backward"></i><?php 
}

if ($page < $numPages) {
    ?><a class="fa fa-forward"  style="text-decoration: none; padding: 5px" href="#"
	onclick="browse<?php echo $tableDiv; ?>('<?php echo $phpTable; ?>', '#<?php echo $tableDiv; ?>', <?php echo $offset + $limit; ?>, <?php echo $limit; ?>)"></a><?php  
} else {
    ?><i style="color: #BBBBBB; padding: 5px" class="fa fa-forward"></i><?php 
}

if ($numPages > 1 && $page < $numPages) {
    ?><a class="fa fa-fast-forward"  style="text-decoration: none; padding: 5px" href="#"
    	onclick="browse<?php echo $tableDiv; ?>('<?php echo $phpTable; ?>', '#<?php echo $tableDiv; ?>', <?php echo floor($numRighe / $limit) * $limit ; ?>, <?php echo $limit; ?>)"></a><?php      
} else {
    ?><i style="color: #BBBBBB; padding: 5px" class="fa fa-fast-forward"></i>&nbsp;&nbsp;<?php 
}

if ($numRighe > $minLimit) {
    ?>
Number of results per page:
<select
	onchange="browse<?php echo $tableDiv; ?>('<?php echo $phpTable; ?>', '#<?php echo $tableDiv; ?>',  0, this.value)">
<?php
foreach ($limits as $l) {
    ?>
<option value="<?php echo $l; ?>"
		<?php if ($l == $limit) { echo "selected"; }?>><?php echo $l; ?></option>
<?php } 
}?>
</select>

</div>