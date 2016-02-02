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

require ('../../config.php');

// Should be called at the begining of each table script
if (false == isset($con)) {
    require ('../dbaccess.php');
}


// Limits available for display on screen
$minLimit = 10;


$convArr = array(
    1 => "<span style=\"color: #008000;font-weight:bold\">OK</span>",
    0 => "<span style=\"color: #FF0000;font-weight:bold;\">NO</span>"
);

$mergArr = array(
    0 => "<span style=\"color: ##0288AD; font-weight: bold\">lims</span>",
    1 => "<span style=\"color: orange; font-weight: bold\">merged</span>",
    2 => "<span style=\"color: green; font-weight: bold\">ext.</span>"
);

function printStatus($status) {
    if ($status == 'completed') {
         echo '<a href="#" title="' . $status . '" class="fa fa-thumbs-up"></i>'; 
    } else  if ($status == 'deleted') {
         echo '<a href="#" title="' . $status . '" class="fa fa-times" style="color: grey"></a>'; 
    } else  if (strpos($status, 'Error') === 0) {
         echo '<a href="#" title="' . $status . '" class="fa fa-thumbs-down" style="color: red"></a>'; 
    } else if (strpos($status, 'wait') === 0 || $status == 'queued') {
         echo '<a href="#" title="' . $status . '" class="fa fa-clock-o" style="color: grey"></a>'; 
    } else  {
    	echo '<a href="#" title="' . $status . '" style="color: grey" class="fa fa-gear"></a>'; 
    } 
}


if (isset($_POST["offset"])) {
    $offset = $_POST["offset"];
} else {
    $offset = 0;
}

if (isset($_POST["limit"])) {
    $limit = $_POST["limit"];
} else {
    $limit = $minLimit;
}

if (isset($_POST["selectable"])) {
    $selectable = ($_POST["selectable"] != 'false');
} else {
    $selectable = false;
}


if (isset($_POST["editable"])) {
    $editable = ($_POST["editable"] != 'false');
} else {
    $editable = false;
}


if (isset($_POST["selectedIds"])) {
    $selectedIds = $_POST["selectedIds"];
    error_log("Selected ids: " . $selectedIds);
} else {
    error_log("Selected ids not defined");
    $selectedIds = "";
}



$limits = array(
    $minLimit,
    50,
    100,
    "all"
);

if (isset($limit) && $limit != "all") {
    $pagination = " LIMIT " . $offset.",".$limit;
} else {
    $pagination = "";
}

if ($selectable) {
?>
<div id="selectIdsContainer">
<?php 
$values = explode(" ", $selectedIds);
foreach ($values as $selectedId) {
    $value =   preg_replace("/[\'\ ]/", "", $selectedId);
    if ($value != "") {         
        ?><div id="select<?php echo $value; ?>" style="display: inline-block;" class="filtertable" id="<?php echo $value; ?>" ><?php echo $value; ?><a class="fa fa-times" onclick="unSelectElement('<?php echo $value; ?>', '#selectedIds')"></a></div><?php 
    }
}
?>
</div>
<input type="hidden" id="selectedIds" name="selectedIds" value="<?php echo $selectedIds; ?>" style="border: none; width:100%" disabled/>
<?php 
} else {
    $selectedSamples = array();
    $values = explode(" ", $selectedIds);
    foreach ($values as $selectedId) {
        $value =   preg_replace("/[\'\ ]/", "", $selectedId);
        if ($value != "") {
            array_push($selectedSamples, $value);     
        }
    }
    ?>
    <input type="hidden" id=selectedIds name="selectedIds" value="<?php echo $selectedIds; ?>"/>
    <?php 
}



?>
<script>
function updateSelectedIds(isChecked, id, div) {
	if (isChecked) {
		selectElement(id, "#" + div) ;
	} else {
		unSelectElement(id, "#" + div);
	} 
}


function selectElement(id, div) {	
	$(div).val($(div).val() + "'"+id+"' ");
	
	$("#selectIdsContainer").append('<div id="select' + id + '" style="display: inline-block;" class="filtertable" id="' + id + '" >' + id + '<a class="fa fa-times" onclick="unSelectElement(\''+ id +'\', \''+div+'\')"></a></div>');
}

function unSelectElement(id, div) {	
	$("#select" + id).remove();
	$("#selected_" + id).attr('checked', false);
	$(div).val($(div).val().replace( "'"+id+"'", '')); 
}
</script>
<?php 
