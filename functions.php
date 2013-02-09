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
    $bldQuery = "SELECT * FROM $tableName;";
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
    echo "Internal function problem.";
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

/* This function is specifically designed to check for duplicate entries in the
 * members table.  It accepts a surname and a given name and, optionally, a middle
 * name and/or suffix as arguments.  It returns whether or not the name is already
 * in the database.
 */
function checkMemberExists($surName, $givenName, $middleName, $suffix)
{
  if(!$surName || !$givenName)
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
    echo "Internal function error.";
  }
  else
  {
    if($middleName)
      $addMiddle = " AND middlename=?";
    if($suffix)
      $addSuffix = " AND suffix=?";
    $dbLink = dbconnect();
    $bldQuery = "SELECT * FROM members WHERE surname=? AND givenname=?";
    if($addMiddle)
      $bldQuery = $bldQuery . $addMiddle;
    if($addSuffix)
      $bldQuery = $bldQuery . $addSuffix;
    $statement = $dbLink->prepare($bldQuery);
    if($middleName && $suffix)
      $statement->execute(array($surName, $givenName, $middleName, $suffix));
    elseif($middleName && !$suffix)
      $statement->execute(array($surName, $givenName, $middleName));
    elseif(!$middleName && $suffix)
      $statement->execute(array($surName, $givenName, $suffix));
    else
      $statement->execute(array($surName, $givenName));
    $numRows = $statement->rowCount();
    if(!$numRows)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Member not found in database.";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "$numRows members matching search found in database.";
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
  $tmpVar = checkExists("positions", "assignment", $position);
  $positionExists = $tmpVar['RSLT'];
  if($tmpVar['MSSG'] == "Incomplete data set passed.")
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Internal function error: " . $tmpVar['MSSG'];
  }
  else
  {
    if($positionExists)
    {
      try
      {
        $dbLink = dbconnect();
        $bldQuery = "INSERT INTO positions(assignment) VALUES('$position');";
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
  }
  return $r_val;
}

/* This function inserts records into the various types tables.  The types tables
 * maintain the simple records of the various types of telephone numbers, email
 * addresses.  It accepts two arguments, a table name and a label.  It returns 
 * whether or not the insert of the new record was successful.
 */
function addType($tableName, $labelContent)
{
  $tmpVar = checkExists($tableName, "label", $labelContent);
  $typeExists = $tmpVar['RSLT'];
  if($tmpVar['MSSG'] == "Incomplete data set passed.")
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Internal function error: " . $tmpVar['MSSG'];
  }
  else
  {
    if($typeExists)
    {
      try
      {
        $dbLink = dbconnect();
        $bldQuery = "INSERT INTO $tableName(label) VALUES('$labelContent');";
        $statement = $dbLink->prepare($bldQuery);
        $statement->execute();
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "New record inserted into $tableName";
      }
      catch(PDOException $exception)
      {
        echo "Unable to insert new record into $tableName.  Sorry.";
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = $exception->getMessage();
      }
    }
    else
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Position already present in database.";
    }
  }
  return $r_val;
}
?>
