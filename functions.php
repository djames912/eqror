<?php
/* Require the connectdb.php file since it contains the necessary information to
 * make a connection to the database.
 */
require_once "connectDB.php";

/* This is a generic function that pulls the entire contents of a table.  It accepts
 * as an argument the name of the table and returns an associative array of the
 * contents of the table.  It's meant to be used as a testing/debugging tool.
 */
function getTableContents($tableName)
{
  try
  {
    $dbLink = dbconnect();
    $bldQuery = "SELECT * FROM $tableName";
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $r_val['RSLT'] = "0";
    $r_val['MSSG'] = $statement->fetchAll(PDO::FETCH_ASSOC);
  }
  catch(PDOException $exception)
  {
    echo "Oooops.  Unable to get your data.";
    $r_val['RSLT'] =  "1"; 
    $r_val['MSSG'] = $exception->getMessage();
  }
  return $r_val;
}

/* This function checks to see if a given value already exists in a given table
 * It accepts three arguments: the table name, the colum name and the value being
 * searched for.  This function may be expanded on and made to be more sophisticated
 * in the future.
 */
function checkExists($tableName, $fieldName, $searchFor)
{
  if(!$tableName || !$fieldName || !$searchFor)
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
 else
  {
    $dbLink = dbconnect();
    $bldQuery = "SELECT * FROM $tableName WHERE $fieldName='$searchFor';";
    $statement = $dbLink->query($bldQuery);
    $row_count = $statement->rowCount();
    if(!$row_count)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "$searchFor does not exist in $tableName";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "$row_count entries for $searchFor found in $tableName.";
    }
  }
  return $r_val;
}

/* This function inserts records into the positions table.  It accepts as an
 * argument the name of the position that needs to be created.  It returns
 * whether or not the insert was successful.
 */
function addPosition($position)
{
  $tmpVar = checkExists(positions, assignment, $position);
  $positionExists = $tmpVar['RSLT'];
  if($positionExists)
  {
    try
    {
      $dbLink = dbconnect();
      $bldQuery = "INSERT INTO positions (assignment) VALUES ('$position');";
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "New position successfully inserted.";
    }
    catch(PDOException $exception)
    {
      echo "Unable to insert the new position.  Sorry.";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = $exception->getMessage();
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Position already present in database.";
  }
  return $r_val;
}

?>
