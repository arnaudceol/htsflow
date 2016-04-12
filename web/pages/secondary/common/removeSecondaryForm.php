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

$id = $_POST['id'];

require ('../../../config.php');

// Should be called at the begining of each table script
if (false == isset($con)) {
    require ('../../dbaccess.php');
}
$isReferencesd= FALSE;
/* 
// Is it used?
$sqlForeignKeys = "SELECT TABLE_NAME, COLUMN_NAME
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = 'secondary_analysis'
  AND REFERENCED_COLUMN_NAME = 'id';";

$result = mysqli_query($con, $sqlForeignKeys);


$queries = array();
while ($row = mysqli_fetch_assoc($result)) {
    array_push($queries, "SELECT " . $row['COLUMN_NAME'] . " as id FROM " . $row['TABLE_NAME']);    
}

$query = implode(" UNION ", $queries);

echo $query;

$result = mysqli_query($con, $query); */
/* 

while ($row = mysqli_fetch_assoc($result)) {    
    if ($id == $row['id']) {
        $isReferenced = TRUE;
        break;
    }
} */

if ($isReferenced) {
    ?>
	<i class="fa fa-exclamation-triangle"></i>
	The results of this analysis have been used for secondary analyses. It cannot be deleted.   
    <?php 
} else {

?>
Delete all data for secondary analysis: <b><?php echo $id; ?></b>?
<br /><i class="fa fa-exclamation-triangle" style="color: red"></i>
<form style="display: inline" action="pages/secondary/common/submitRemove.php"
	name="submitDelete_<?php echo $id; ?>" method="post">
	<input type="submit" value="Confirm" name="submitDeleteSecondary" /> <input
		type="hidden" name="ID" value="<?php echo $id; ?>" />
</form>
<script>

</script>
<?php } ?>