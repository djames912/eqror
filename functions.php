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
    $r_val = $statement->fetchAll(PDO::FETCH_ASSOC);
  }
  catch(PDOException $exception)
  {
    echo "Oooops.  Unable to get your data.";
    $r_val['RSLT'] = $exception->getMessage();
  }
  return $r_val;
}

/* This function inserts records into the positions table.  It accepts as an
 * argument the name of the position that needs to be created.  It returns
 * whether or not the insert was successful.
 */
function addPosition($position)
{
  try
  {
    $dbLink = dbconnect();
    $bldQuery = "INSERT INTO positions (assignment) VALUES ('$position');";
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
  }
  catch(PDOException $exception)
  {
    echo "Unable to insert the new position.  Sorry.";
    $r_val = $exception->getMessage();
  }
  return $r_val;
}

?>
