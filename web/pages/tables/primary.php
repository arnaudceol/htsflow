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
include 'tableCommons.php';

// Need as input
// 1. the sql query: the result of the mysql query: $result
// 2. a boolean to indicate if rows are selectable: $selectable
// 3. the id of the table: $tableId
// $result = mysqli_query ( $con, $sql );
// $numRighe = $result->num_rows;

$baseQuery = "SELECT  description, dateStart, dateEnd, CASE WHEN status = 'completed' THEN timediff( dateEnd, dateStart )  ELSE timediff( NOW(), dateStart ) END AS time, options_id, primary_analysis.id as id_pre, sample_id as id_sample_fk, reads_num, raw_reads_num, primary_analysis.status, ref_genome, users.user_name, sample_owner.user_name as sample_owner,seq_method, origin AS SOURCE, sample_name, reads_mode, raw_data_path FROM primary_analysis, pa_options, users, sample, users sample_owner WHERE sample.id = primary_analysis.sample_id AND pa_options.id = primary_analysis.options_id and primary_analysis.user_id = users.user_id  and sample_owner.user_id = sample.user_id" ;

$typeToQuery = array(
    'completed' => $baseQuery . " AND primary_analysis.status='completed'",
    'running' => $baseQuery . "  AND primary_analysis.status!='completed'",
    'all'  => $baseQuery 
);


if (isset($_POST["type"]) && file_exists ( '../../pages/secondary/' . $_POST["type"] . '/primary_sql_filter.php' ) ) {
    // add an entry to typeToQuery
    include '../../pages/secondary/' . $_POST["type"] . '/primary_sql_filter.php';
    $typeToQuery[$_POST["type"]] = $primarySqlFilter;    
}



$concatArray = array();

if (isset($_POST['ref_genome']) && $_POST['ref_genome'] != "") {
    $ref_genome = " ref_genome=\"" . trim($_POST['ref_genome']) . "\"";
    array_push($concatArray, $ref_genome);
} 
if (isset($_POST['user_id']) && $_POST['user_id'] != "") {
    $user_id = " users.user_id=" . $_POST['user_id'] . "";
    array_push($concatArray, $user_id);
} 

if (isset($_POST['sample_owner']) && $_POST['sample_owner'] != "") {
    $user_id = " sample_owner.user_id=" . $_POST['sample_owner'] . "";
    array_push($concatArray, $user_id);
}

if (isset($_POST['source']) && $_POST['source'] != "") {
    $source = " source=" . $_POST['source'] . "";
    array_push($concatArray, $source);
} 



// Looking for secondary based on a primary one?
$secondaryIds = array();

if (isset($_REQUEST['secondaryId']) && $_REQUEST['secondaryId'] != "") {
	$secondaryIds = array_merge($secondaryIds , explode(" ", preg_replace('/\s+/', ' ',trim(strtoupper($_REQUEST['secondaryId'])))));
}

if (sizeof($secondaryIds) > 0) {
	$secondaryIdSql = " primary_analysis.id IN ";

	$secondaryToPrimarySqls = array();

	$secondaryId = implode(", ", $secondaryIds);

	foreach (scandir("../secondary/") as $type_secondary) {
		if ($type_secondary != ".." && $type_secondary != "." && $type_secondary != 'common') {
			if (file_exists('../secondary/'. $type_secondary . '/secondary_to_primary.php')) {
				include '../secondary/'. $type_secondary . '/secondary_to_primary.php';
				array_push($secondaryToPrimarySqls, $secondaryToPrimarySql);
			} else {
				include '../secondary/common/secondary_to_primary.php';
				array_push($secondaryToPrimarySqls, $secondaryToPrimarySql);
			}
		}
	}

	$secondaryIdSql .= "(" . implode ( " UNION ", $secondaryToPrimarySqls ). ")";

	array_push($concatArray, $secondaryIdSql);
}


if (isset($_POST['primaryId']) && $_POST['primaryId'] != "") {
    $querySampleId = "UPPER(primary_analysis.id) ='" . strtoupper($_POST['primaryId']) . "'";
    array_push($concatArray, $querySampleId);
}

if (isset($_REQUEST['sampleId']) && $_REQUEST['sampleId'] != "") {
    // there may be more than one sample: split the field
    $sampleIds = explode(" ", preg_replace('/\s+/', ' ',trim(strtoupper($_REQUEST['sampleId']))));    
    $querySampleId = "UPPER(sample_id) IN ('" . implode("', '", $sampleIds) . "')";
    array_push($concatArray, $querySampleId);    
}

if (isset($_POST['sampleName']) && $_POST['sampleName'] != "") {
    $querySampleName = "UPPER(sample_name) like '%" . strtoupper($_POST['sampleName']) . "%'";
    array_push($concatArray, $querySampleName);
}

if (isset($_POST['seqMethod']) && $_POST['seqMethod'] != "") {
	$querySeqMethod = "seq_method = '" . $_POST['seqMethod'] . "'";
	array_push($concatArray, $querySeqMethod);
}

if (isset($_POST['description']) && $_POST['description'] != "") {
    $queryDescription = "UPPER(description) like  '%" .strtoupper($_POST['description']) . "%'";
    array_push($concatArray, $queryDescription);
}


if (isset($_POST['status']) && $_POST['status'] != "") {
    if ($_POST['status'] ==  "completed") {
        $status="status='completed'";
        array_push($concatArray, $status);
    } elseif ($_POST['status'] ==  "running") {
        $status="(status!='completed' AND status <> 'deleted' AND status NOT like 'Error%')";
        array_push($concatArray, $status);
    }  elseif ($_POST['status'] ==  "deleted") {
        $status="(status ='deleted')";
        array_push($concatArray, $status);
    } elseif ( $_POST['status'] != "all") { // error
        $status="status like 'Error%'";
        array_push($concatArray, $status);
    }
} else {
	$status="(status <> 'deleted')";
    array_push($concatArray, $status);
}





// extendedTable: show all options in columns + start/end time
// summary tables: click to see options
if (isset($_POST["extendedTable"])) {
    $extendedTable = $_POST["extendedTable"];
} else {
    $extendedTable = false;
}

$pageURL = 'http';
if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
} else {
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}

// Go to root of website: remove pages/tables/
$igbScript = str_replace("pages/tables", "", dirname($pageURL));

// Prepare query
// $type="footprint";
if (isset($selectedSamples) && sizeof($selectedSamples) > 0) {
    $tmpSTR = "";
    for($i = 0; $i < sizeof ( $selectedSamples ); $i ++) {
        if ($i == 0) {
            $tmpSTR .= " and (primary_analysis.id='" . $selectedSamples [$i] . "'";
        }
        if ($i != 0) {
            $tmpSTR .= " or primary_analysis.id ='" . $selectedSamples [$i] . "'";
        }
    }
    $tmpSTR .= ")";
    $sql = $typeToQuery['all'] . $tmpSTR;
} elseif (isset($_POST["type"])) {
    $sql = $typeToQuery[$_POST["type"]]; // . " OFFSET $offset LIMIT $limit;";
} elseif (isset($type)) {
    $sql = $typeToQuery[$type]; // . " OFFSET $offset LIMIT $limit;";
} else {
    $sql = $typeToQuery['all']; 
}

$filtArray = array_filter($concatArray);
$numOfelements = count($filtArray);
global $con;
switch ($numOfelements) {
    case 0:
        break;
    default:
        $sql = $sql . " AND " . implode(" AND ", $filtArray);
        break;
//     default:
//         $sql = "SELECT s.*, u.user_name FROM sample s, users u WHERE s.user_id = u.user_id AND " . implode(" AND ", $filtArray) . " ORDER BY id_sample DESC";
//         break;
}

$result = mysqli_query($con, "SELECT COUNT(*) FROM (". $sql . ") as g" );

$count=$result->fetch_row();
$result->close();
$numRighe= $count[0];
$result = mysqli_query($con, $sql .  " ORDER BY primary_analysis.id desc " . $pagination);


// Get list of merging
$sqlMerged = "SELECT result_primary_id FROM merged_primary;";

$mergedQuery = mysqli_query($con, $sqlMerged );
$mergedIds = array();
while($mergedResult = mysqli_fetch_array($mergedQuery)) {
    $mergedIds[] = $mergedResult[0];
}
mysqli_free_result($mergedQuery);

// primary id involved in seconadry
// Is it used?
$sqlForeignKeys = "SELECT TABLE_NAME, COLUMN_NAME
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = 'primary_analysis'
  AND REFERENCED_COLUMN_NAME = 'id';";

$resultFK = mysqli_query($con, $sqlForeignKeys);

$queries = array();
while ($row = mysqli_fetch_assoc($resultFK)) {
    array_push($queries, "SELECT " . $row['COLUMN_NAME'] . " as id FROM " . $row['TABLE_NAME']);
}

mysqli_free_result($resultFK);


$inSecondarySql= implode(" UNION ", $queries);

$inSecondaryQuery = mysqli_query($con, $inSecondarySql);

$inSecondaryIds = array();
while ($inSecondaryResult = mysqli_fetch_array($inSecondaryQuery)) {
    $inSecondaryIds[] = $inSecondaryResult[0];
}
mysqli_free_result($inSecondaryQuery);
?>
  
<script>
function igbLoad(id) {
	url = "http://localhost:7085/UnibrowControl?scriptfile=<?php echo $igbScript; ?>/igb.php?id%3d" + id + "/primary.igb";
/* 	$.ajax({
	    type: 'HEAD',
	    url: url,
	    success: function() {
	    	alert('success');    
	    },
	    error: function() {
	    	toggle('igbDialog');
	    }	 
	}); */
	toggle("igbLoadIcon"+id);
	var jqxhr = $.get( url, function() {
		toggle("igbLoadIcon"+id);
		}).fail(function() {
			toggle('igbDialog');
			toggle("igbLoadIcon"+id);
		 })
	;
		
}

function ucscGbLoad(id, genome) {
	ucscScript = "<?php echo str_replace("pages/tables", "", dirname($pageURL)) . "ucscgb.php"; ?>";
	url="<?php echo $HTSFLOW_PATHS['UCSC_GENOME_BROWSER_URL']?>/cgi-bin/hgTracks?db=" + genome + "&hgt.customText=" + ucscScript + "<?php echo urlencode("?id="); ?>" +  id + "<?php echo urlencode("&type=primary") ; ?>";
	//alert(url)
	//url = ucscScript + "?id=" + id + "&type=primary"
	window.open(url, '_gb');	
	return false;
}

</script>


<div id="igbDialog" title="IGB dialog" style="display: none; vertical-align: middle" class="popupstyle">
  <img src="images/igb.jpg"/> Please lauch IGB before to click this link. IGB can be downloaded at <a href="http://bioviz.org/igb/index.html">http://bioviz.org/igb/index.html</a>.
  <a style="float: right;margin: 4px;" class="fa fa-times" href="#"  onclick="javascript:toggle('igbDialog'); "></a>
</div>

<div class="datagrid">
	<div class="table-container">
	<?php
$tableDiv = "tablePrimary";
$phpTable = "primary.php";

if (! isset($_REQUEST["browsable"]) || $_REQUEST["browsable"] == false) {
	include 'browseScripts.php';
}

?>
		<table class="mytable filterable" id="sf">
			<thead >
				<tr>
					<?php if ($selectable) { ?>
					<th></th>
					<?php } ?>					
					<th class="centered">PRIMARY ID</th>
					<th style="width: 0%"></th>
					<th class="centered">SAMPLE ID</th>
					<th style="text-align: left">Sample name</th>
					<th class="centered">READS NUM (raw/aligned)</th>
					<th class="centered">REF GENOME</th>
					<th class="centered">METHOD</th>
					<th class="centered">SOURCE</th>			
					<th class="centered">SAMPLE SUBMITTER</th>								
					<th style="text-align: right">USER</th>
					<th></th>		
				</tr>
			</thead>
			<tbody>
<?php

$pageURL = 'http';
if (isset ( $_SERVER ["HTTPS"] ) && $_SERVER ["HTTPS"] == "on") {
	$pageURL .= "s";
}
$pageURL .= "://";
if ($_SERVER ["SERVER_PORT"] != "80") {
	$pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"];
} else {
	$pageURL .= $_SERVER ["SERVER_NAME"];
}


while ($row = mysqli_fetch_assoc($result)) {
        ?><tr <?php if ($row['status'] == "deleted") { echo " style=\"color: grey\""; }?>>
	<?php if ($selectable) { ?>
				<td style="text-align: left"><?php if($row["status"] == "completed") { 
				?><input type="checkbox" id="selected_<?php echo $row["id_pre"]; ?>"
						name="selected_<?php echo $row["id_pre"]; ?>"
						onclick="updateSelectedIds($(this).is(':checked'), '<?php echo $row["id_pre"]; ?>', 'selectedIds')"
						value="<?php echo $row["id_pre"]; ?>"
						 <?php if (strpos($selectedIds,"'".$row["id_pre"]."'") !== false ) { echo "checked"; } ?>/><?php 
				}?></td>
	<?php } ?>
				<td class="centered"><?php echo $row["id_pre"]; ?> <?php printStatus($row['status']); ?></td>
				
				<td><?php 
				$description = $row['description'];
				
				$class ="";
				if ($description != '') {
				        $class = "title=\"sample description, click to edit\" class=\"fa fa-file-text-o\" style=\"color: green\"";				        
				} else {
				        $class = "title=\"sample description missing, click to edit\" class=\"fa fa-file-o\" style=\"color: red\""; 
				}
				
				?><a <?php echo $class; ?>  href='#'
									onclick='javascript:toggle("submitDescription_<?php echo $row["id_pre"]; ?>")'></a>
										<form action="#" style="display: none; " class="popupstyle"
											id="submitDescription_<?php echo $row["id_pre"]; ?>"
											method="POST">
											<a style="float: right;margin: 4px;" class="fa" href="#"  onclick="javascript:toggle('submitDescription_<?php echo $row["id_pre"]; ?>'); ">close <i  class="fa fa-times"></i></a>
											<table>
												<tbody style="vertical-align: top">
													<tr>
														<td ><textarea rows="30" cols="100" name="TEXTdescription" id="TEXTdescription_<?php echo $row["id_pre"]; ?>" ><?php echo $description; ?></textarea>
														</td>
														<td ><input type="hidden" name="ID"
															value="<?php echo $row["id_pre"]; ?>" /><input type="submit" value="Submit"
															name="submitDescriptionPrimary" onclick="$.post('pages/primary/submitDescription.php', $('#submitDescription_<?php echo $row["id_pre"]; ?>').serialize($('#TEXTdescription<?php echo $row["id_pre"]; ?>'))); refreshTable(); return false;" />
														</td>
													</tr>
												</tbody>
											</table>
										</form><?php
            
            // $convArr = array(1 => "TRUE", 0 => "FALSE");
            $convArr = array(
                1 => "<span style=\"color: #008000;font-weight:bold\">YES</span>",
                0 => "<span style=\"color: #FF0000;font-weight:bold;\">NO</span>"
            );
            $query = "SELECT * FROM pa_options where id = \"" . $row["options_id"] . "\"";
            $res = mysqli_query($con, $query);
            while ($row_details = mysqli_fetch_assoc($res)) {
                ?>
                	<a title="show details" id="ICON_<?php echo $row["id_pre"]; ?>" class="fa fa-info" href="#" onclick="javascript:toggle('OPTIONS_<?php echo $row["id_pre"]; ?>');"></a>                	                	
                		<div id="OPTIONS_<?php echo $row["id_pre"]; ?>"
							style="display: none" class="popupstyle">
							<a style="float: right;margin: 4px;" class="fa fa-times" href="#"  onclick="javascript:toggle('OPTIONS_<?php echo $row["id_pre"]; ?>'); "></a>
							<p>
							<b>Start: </b><?php echo $row["dateStart"];  ?><br/>
							<b>End: </b><?php if ($row["dateEnd"] != "") { echo $row["dateEnd"]; } else {echo "-"; }; ?><br/>
							<b>Time (hh:mm:ss): </b><?php echo $row['time'];  ?><br/>
							</p>
							<div><b>Options: </b></div>
							<table style="text-align: left">
								<tbody>
									<tr>
										<th>remove bad reads</th>
										<td><?php echo $convArr[$row_details["rm_bad_reads"]]; ?></td>
									</tr>
									<tr>
										<th>trimming</th>
										<td><?php echo $convArr[$row_details["trimming"]]; ?></td>
									</tr>
									<tr>
										<th>masking</th>
										<td><?php echo $convArr[$row_details["masking"]]; ?></td>
									</tr>
									<tr>
										<th>alignment</th>
										<td><?php echo $convArr[$row_details["alignment"]]; ?></td>
									</tr>
									<tr>
										<th>aln_program</th>
										<td><?php echo $row_details["aln_prog"]; ?></td>
									</tr>
									<tr>
										<th>aln_options</th>
										<td><?php echo $row_details["aln_options"]; ?></td>
									</tr>
									<tr>
										<th>paired</th>
										<td><?php echo $convArr[$row_details["paired"]]; ?></td>
									</tr>
									<tr>
										<th>remove duplicates</th>
										<td><?php echo $convArr[$row_details["rm_duplicates"]]; ?></td>
									</tr>
									<tr>
										<th>Stranded</th>
										<td><?php $stranded = $row_details["stranded"];  echo $convArr[$row_details["stranded"]];  ?></td>
									</tr>
								</tbody>
							</table>
						</div><?php
            }
            mysqli_free_result($res);
            
            // Merge info
            
            
            
            ?><?php if ($row["status"] == "completed") { 
            		if (! in_array($row ["id_pre"], $mergedIds)) { ?>
							<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/QC/<?php  echo $row ["id_pre"]; ?>_fastqc/fastqc_report.html" ><img src="images/fastqc_icon.png" width="12" title="Browse FastQC Report"/></a>
 						<!--	<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/QC/<?php  echo $row ["id_pre"]; ?>_fastqc.zip" ><i title="Download FastQC Report" class="fa fa-download"></i></a> -->
					<?php } ?>
							<a href="<?php echo $pageURL . "/" .$HTSFLOW_PATHS['HTSFLOW_WEB_TRACKS']; ?>/primary/tracks/bw/<?php  echo $row ["id_pre"]; ?>.bw" ><i title="Download BigWig" class="fa fa-download"></i></a>
							<a href="#" title="Load track in UCSC Genome Browser" onclick="ucscGbLoad('<?php  echo $row ["id_pre"]; ?>', '<?php echo $row["ref_genome"]; ?>')"><img height=16" src="images/ucsc-genome-browser.png"/></a>
  							<span class="fa-stack " >  
								<a href="#" title="Load track in IGB" onclick="igbLoad('<?php  echo $row ["id_pre"]; ?>')"><img height=16" src="images/igb.jpg"/></a>								
								<a class="fa fa-refresh fa-stack-1x fa-spin" id="igbLoadIcon<?php  echo $row ["id_pre"]; ?>" style="display: none;"></a> 
							</span>	
							<?php if ( $stranded == 1  ) { ?>
								<i class="fa fa-exchange" style="color: green" aria-hidden="true" title="stranded"></i>								
							<?php 
            			} else {
            				?><i class="fa fa-long-arrow-right" aria-hidden="true" title="unstranded"></i><?php 
            			}
            		}?>
					</td>	
		
					<td class="centered"><a href="samples.php?sampleId=<?php echo $row["id_sample_fk"]; ?>"><?php echo $row["id_sample_fk"]; ?></a></td>
					<td><?php echo $row["sample_name"]; ?></td>
					<td class="centered"><span <?php if (isset($row["reads_num"]) && intval($row["reads_num"]) > 200000000) { echo "style=\"color: #CC9900\" title=\"High definition sample: > 200,000,000 aligned reads\"";} ?>"><?php echo (isset($row["raw_reads_num"]) ? number_format($row["raw_reads_num"]) : " - ")  . " / " . (isset($row["reads_num"]) ? number_format($row["reads_num"]) : " - "); if (isset($row["reads_num"]) && intval($row["reads_num"]) > 200000000) { echo " (hd)"; }?></span></td>
								<td class="centered"><?php echo $row["ref_genome"]; ?></td>
					<td class="centered method"><?php echo $row["seq_method"]; ?></td>
					<td class="centered"><?php echo $mergArr[$row["SOURCE"]]; 
					   // merging
					   if ($row["SOURCE"] == 1) {
				        ?><a  href="#" id="ICON_MERGE_<?php echo $row["id_pre"]; ?>" title="Go to sample" class="fa fa-info" onclick="javascript:toggle('MERGE_<?php echo $row["id_pre"]; ?>'); $(this).toggleClass('fa-info');$(this).toggleClass('fa-info-slash')"></a>
				        <div id="MERGE_<?php echo $row["id_pre"]; ?>" style="display: none" class="popupstyle">
				        	<a style="float: right;margin: 4px;" class="fa fa-times" href="#"  onclick="javascript:toggle('MERGE_<?php echo $row["id_pre"]; ?>'); $('#ICON_MERGE_<?php echo $row["id_pre"];?>').toggleClass('fa-info');$('#ICON_MERGE_<?php echo $row["id_pre"]; ?>').toggleClass('fa-info-slash')"></a>
				        	<?php
				        	   $mergedPrimaryId = $row["id_pre"];
				        	   include 'mergeDetails.php';
				        	?>
				        </div>
				        <?php 
				    } 
				    // Downsampling
				    if ($row["SOURCE"] == 3) {
				    	?><a  href="#" id="ICON_MERGE_<?php echo $row["id_pre"]; ?>" title="Go to sample" class="fa fa-info" onclick="javascript:toggle('MERGE_<?php echo $row["id_pre"]; ?>'); $(this).toggleClass('fa-info');$(this).toggleClass('fa-info-slash')"></a>
				    				        <div id="MERGE_<?php echo $row["id_pre"]; ?>" style="display: none" class="popupstyle">
				    				        	<a style="float: right;margin: 4px;" class="fa fa-times" href="#"  onclick="javascript:toggle('MERGE_<?php echo $row["id_pre"]; ?>'); $('#ICON_MERGE_<?php echo $row["id_pre"];?>').toggleClass('fa-info');$('#ICON_MERGE_<?php echo $row["id_pre"]; ?>').toggleClass('fa-info-slash')"></a>
				    				        	<?php
				    				        	   $mergedPrimaryId = $row["id_pre"];
				    				        	   include 'downsamplingDetails.php';
				    				        	?>
				    				        </div>
				    				        <?php 
				    				    } 
				    
				    ?></td>					
					<td class="centered"><?php echo $row["sample_owner"]; ?></td>
					<td style="text-align: right"><?php echo $row["user_name"];  ?>
					<a href="<?php echo $HTSFLOW_PATHS['HTSFLOW_WEB_OUTPUT']; ?>/users/<?php 
					   echo $row["user_name"];  
					   if (in_array($row ["id_pre"], $mergedIds)) {
					       echo '/M';
					   } else {
					       echo "/P";
					   }
					   echo $row["id_pre"];
				    ?>" ><i title="Show logs"class="fa fa-newspaper-o"></i></a>
					</td><td width="10px">
					<?php $allowUserDelete = false;
					$allowAdminDelete = false;
					if ($_SESSION['grantedAdmin'] == 1  && $row['status'] != 'deleted') {
					    $allowAdminDelete = true;
					}
					if ($row["user_name"] == $_SESSION["hf_user_name"] 
					    &&  ($row['status'] == 'completed' || strpos($row['status'], 'Error') === 0)) {
					    $allowUserDelete = true;
					}
					
					if ($allowUserDelete || $allowAdminDelete) { ?>
					<a style="float: right;margin: 4px;<?php if (!$allowUserDelete) { echo "color: red"; } ?>" title="Delete" class="fa fa-eraser" href="#"  onclick="$.post('pages/primary/removePrimaryForm.php', {id: '<?php echo $row['id_pre']; ?>', }, function(response) { $( '#DELETE_<?php echo $row["id_pre"]; ?>_FORM' ).html(response);});javascript:toggle('DELETE_<?php echo $row["id_pre"]; ?>');"></a>
					<div id="DELETE_<?php echo $row["id_pre"]; ?>"
							style="display: none" class="popupstyle">
							<div style="display: inline" id="DELETE_<?php echo $row["id_pre"]; ?>_FORM"></div>
							<input type="submit" value="Cancel"  onclick="javascript:toggle('DELETE_<?php echo $row["id_pre"]; ?>'); return false"/></div>
						<?php  } 
						if (($_SESSION['grantedAdmin'] == 1 || $row["user_name"] == $_SESSION["hf_user_name"])
					        && $row['status'] == 'deleted') { ?>				
					<a style="float: right;margin: 4px;" title="Repeat" class="fa fa-repeat" href="#"  onclick="javascript:toggle('REPEAT_<?php echo $row["id_pre"]; ?>');"></a>
					<div id="REPEAT_<?php echo $row["id_pre"]; ?>"
							style="display: none" class="popupstyle">
							<b>Repeat primary analysis: </b><?php echo $row['id_pre']; ?>? <br/>
							<form style="display: inline" action="pages/primary/submitRepeat.php"
										name="submitRepeat_<?php echo $row["id_pre"]; ?>"
										method="post"><input type="submit" value="Confirm"
															name="submitRepeatPrimary" /> <input type="hidden" name="ID"
															value="<?php echo $row["id_pre"]; ?>" />

									</form><input type="submit" value="Cancel"  onclick="javascript:toggle('DELETE_<?php echo $row["id_pre"]; ?>; return false')"/>
									</div>
									<?php  }?>					
					</td>
							
				</tr> <?php
    
}
mysqli_free_result($result);
?>
                </tbody>
		</table>
	
		

	 
		<table>

</table>
	</div>
</div>

