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
    $r_val['MSSG'] = "Data located in $tableName";
    $r_val['DATA'] = $statement->fetchAll(PDO::FETCH_ASSOC);
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
  if(!isset($tableName) || !isset($fieldName) || !isset($searchFor))
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
    if($row_count == 0)
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
function checkMemberExists($surName, $givenName, $middleName = NULL, $suffix = NULL)
{
  $addMiddle = false;
  $addSuffix = false;
  if(!isset($surName) || !isset($givenName))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
    echo "Internal function error.";
  }
  else
  {
    if(!is_null($middleName))
      $addMiddle = " AND middlename=?";
    if(!is_null($suffix))
      $addSuffix = " AND suffix=?";
    $dbLink = dbconnect();
    $bldQuery = "SELECT * FROM members WHERE surname=? AND givenname=?";
    if($addMiddle)
      $bldQuery = $bldQuery . $addMiddle;
    if($addSuffix)
      $bldQuery = $bldQuery . $addSuffix;
    $statement = $dbLink->prepare($bldQuery);
    if(!is_null($middleName) && !is_null($suffix))
      $statement->execute(array($surName, $givenName, $middleName, $suffix));
    elseif(!is_null($middleName) && is_null($suffix))
      $statement->execute(array($surName, $givenName, $middleName));
    elseif(is_null($middleName) && !is_null($suffix))
      $statement->execute(array($surName, $givenName, $suffix));
    else
      $statement->execute(array($surName, $givenName));
    $numRows = $statement->rowCount();
    if($numRows == 0)
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

/* This function adds a new member.  It accepts four arguments: the surname,
 * the given name, an opptional middle name (or initial) and an optional suffix.
 * It returns whether or not the insert was successful or not.
 */
function addMember($surName, $givenName, $middleName = NULL, $suffix = NULL, $preferred = NULL)
{
  $memberExists = false;
  if(!isset($surName) || !isset($givenName))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed: need both a surname and given name.";
  }
  else
  {
    if(is_null($middleName)  && is_null($suffix))
      $tmpVar = checkMemberExists($surName, $givenName);
    elseif(is_null($middleName) && !is_null($suffix))
      $tmpVar = checkMemberExists($surName, $givenName, "", $suffix);
    elseif(!is_null($middleName) && is_null($suffix))
      $tmpVar = checkMemberExists($surName, $givenName, $middleName);
    else
      $tmpVar = checkMemberExists($surName, $givenName, $middleName, $suffix);
    $memberExists = $tmpVar['RSLT'];
    if($tmpVar['MSSG'] == "Incomplete data set passed.")
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Internal function error: " . $tmpVar['MSSG'];
    }
    else
    {
      if($memberExists)
      {
        try
        {
          $dbLink = dbconnect();
          $bldQuery = "INSERT INTO members(surname, givenname, middlename, suffix, preferred)
            VALUES('$surName', '$givenName', '$middleName', '$suffix', '$preferred');";
          $statement = $dbLink->prepare($bldQuery);
          $statement->execute();
          $r_val['RSLT'] = "0";
          $r_val['MSSG'] = "Member successfully inserted into database.";
          $r_val['DATA'] = $dbLink->lastInsertId();
        }
        catch(PDOException $exception)
        {
          echo "Unable in insert new member.  Sorry.";
          $r_val['RSLT'] = "1";
          $r_val['MSSG'] = $exception->getMessage();
        }
      }
      else
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Member already exists in the database.";
      }
    }
  }
  return $r_val;
}

/* This function accepts up to four arguments, though only two are required and it
 * returns the UID of the member being looked for as part of the 'MSSG' member of
 * the array.
 */
function getMemberUID($surName, $givenName, $middleName = NULL, $suffix = NULL)
{
  $addMiddle = false;
  $addSuffix = false;
  if(!isset($surName) || !isset($givenName))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
    echo "Internal function error.";
  }
  else
  {
    if(!is_null($middleName))
      $addMiddle = " AND middlename=?";
    if(!is_null($suffix))
      $addSuffix = " AND suffix=?";
    $dbLink = dbconnect();
    $bldQuery = "SELECT uid FROM members WHERE surname=? AND givenname=?";
    if($addMiddle)
      $bldQuery = $bldQuery . $addMiddle;
    if($addSuffix)
      $bldQuery = $bldQuery . $addSuffix;
    $statement = $dbLink->prepare($bldQuery);
    if(!is_null($middleName) && !is_null($suffix))
      $statement->execute(array($surName, $givenName, $middleName, $suffix));
    elseif(!is_null($middleName) && is_null($suffix))
      $statement->execute(array($surName, $givenName, $middleName));
    elseif(is_null($middleName) && !is_null($suffix))
      $statement->execute(array($surName, $givenName, $suffix));
    else
      $statement->execute(array($surName, $givenName));
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Member not found in database.";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Member found in database.";
      $r_val['DATA'] = $result['0']['uid'];
    }
  }
  return $r_val;
}

/* This function accepts two arguments: the table name and the label that is being
 * searched for in the table.  It returns the type ID of the label.
 */
function getTypeID($tableName, $labelName)
{
  if(!isset($tableName) || !isset($labelName))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    try
    {
      $dbLink = dbconnect();
      $bldQuery = "SELECT typeid FROM $tableName WHERE label='$labelName'";
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchObject();
      if(!$result)
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Label name not found in database.";
      }
      else
      {
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Label name found in database.";
        $r_val['DATA'] = $result->typeid;
      }
    }
    catch(PDOException $exception)
    {
      echo "Unable to retrieve requested data.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = $exception->getMessage();
    }
  }
  return $r_val;
}

/* This function in ths inverse of getTypeID().  This function accepts two arguments
 * a table name and and a label ID.  The function returns the name that matches the
 * label ID number.
 */
function getTypeLabel($tableName, $labelID)
{
  if(!isset($tableName) || !isset($labelID))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    try
    {
      $dbLink = dbconnect();
      $bldQuery = "SELECT label FROM $tableName WHERE typeid='$labelID'";
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchObject();
      if(!$result)
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Lable ID not found in database.";
      }
      else
      {
        $r_val['RSLT'] = "0";
        $r_val['RSLT'] = "Lable ID found in database.";
        $r_val['DATA'] = $result->label;
      }
    }
    catch(PDOException $exception)
    {
      echo "Unable to retrieve requested data.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = $exception->getMessage();
    }
  }
  return $r_val;
}

/* This function adds an email address to the email address table.  It accepts four
 * arguments, three of which are required: a UID, an email address and a type ID.
 * It accepts an optional argument for whether or not the address is the preferred
 * email address.  An email address marked preferred is the one that the event
 * reminder scheduler will select by default.  The function returns whether or not
 * the insert was successful.
 */
function addEmail($UID, $emailAddress, $typeID, $preferred = NULL)
{
  $checkVar = false;
  if(!isset($UID) || !isset($emailAddress) || !isset($typeID))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    $tmpVar = checkExists("email", "emailaddr", $emailAddress);
    $checkVar = $tmpVar['RSLT'];
    if($tmpVar['MSSG'] == "Incomplete data set passed.")
    {
      echo "Internal function error.";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Internal function error: " . $tmpVar['MSSG'];
    }
    else
    {
      if($checkVar)
      {
        if(is_null($preferred))
          $preferred = "0";
        else
          $preferred = "1";
        try
        {
          $bldQuery = "INSERT INTO email(uid, emailaddr, typeid, preferred) VALUES
            ('$UID', '$emailAddress', '$typeID', '$preferred');";
          $dbLink = dbconnect();
          $statement = $dbLink->prepare($bldQuery);
          $statement->execute();
          $r_val['RSLT'] = "0";
          $r_val['MSSG'] = "Email address successfully added.";
        }
        catch(PDOException $exception)
        {
          echo "Unable to insert record into database.";
          $r_val['RSLT'] = "1";
          $r_val['MSSG'] = "Record insert failed: " . $exception->getMessage();
        }
      }
      else
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Email address already exists in database.";
      }
    }
  }
  return $r_val;
}

/* This function assigns positions to members.  It accepts a user ID and a position
 * ID as arguments.  It returns whether or not the entry was successfully made in
 * the database.
 */
function assignPosition($UID, $PID)
{
  if(!isset($UID) || !isset($PID))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    $checkUID = checkExists("members", "uid", $UID);
    $checkPID = checkExists("positions", "id", $PID);
    if($checkUID['RSLT'] == "0" && $checkPID['RSLT'] == "0")
    {
      try
      {
        $bldQuery = "INSERT INTO posholders(uid, pid) VALUES('$UID', '$PID');";
        $dbLink = dbconnect();
        $statement = $dbLink->prepare($bldQuery);
        $statement->execute();
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Insert into database successful.";
      }
      catch(PDOException $exception)
      {
        echo "Unable to insert record into database.";
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Record insert failed: " . $exception->getMessage();
      }
    }
    else
    {
      $r_val['RSLT'] = "1";
      if($checkUID['RSLT'] == "1" && $checkPID['RSLT'] == "1")
        $r_val['MSSG'] = "Neither UID nor PID found in database.";
      elseif($checkPID['RSLT'] == "1")
        $r_val['MSSG'] = "PID not found in database.";
      else
        $r_val['MSSG'] = "UID not found in database.";
    }
  }
  return $r_val;
}

/* This function accepts an object as its argument and returns whether or not the
 * data was successfully entered into the database.
 */
function addEvent($newEvent)
{
  if(!is_object($newEvent))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Object expected.  Something else was passed.";
  }
  else
  {
    if(!(isset($newEvent->title) && isset($newEvent->start) && isset($newEvent->category)))
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Incomplete data set passed.";
    }
    else
    {   
      if(!isset($newEvent->end))
        $newEvent->end = "0";
      if(!isset($netEvent->rid))
        $newEvent->rid = "0";
      try
      {
        $bldQuery = "INSERT INTO events(rid, title, start, end, category)
          VALUES('$newEvent->rid', '$newEvent->title', '$newEvent->start', '$newEvent->end', 
            '$newEvent->category');";
        $dbLink = dbconnect();
        $statement = $dbLink->prepare($bldQuery);
        $statement->execute();
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Insert into database successful.";
        $r_val['DATA'] = $dbLink->lastInsertId();
      }
      catch(PDOException $exception)
      {
        echo "Unable to insert record into database.";
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Record insert failed: " . $exception->getMessage();
      }
    }
  }
  return $r_val;
}

/* This function accepts an event object that contains updated information as an
 * argument and then updates the database contents to match what is contained in
 * the object.
 */
function updateEvent($updatedEvent)
{
  if(!is_object($updatedEvent))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Object expected.  Something else was passed.";
  }
  else
  {
    if(!isset($updatedEvent->eid))
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Missing EID.  Unable to continue.";
    }
    else
    {
      if(!isset($updatedEvent->rid))
        $updatedEvent->rid = "0";
      if(!isset($updatedEvent->end))
        $updatedEvent->rid = "0";
      try
      {
        $bldQuery = "UPDATE events SET rid='$updatedEvent->rid', title='$updatedEvent->title', start='$updatedEvent->start', end='$updatedEvent->end', category='$updatedEvent->category' WHERE eid='$updatedEvent->eid';";
        $dbLink = dbconnect();
        $statement = $dbLink->prepare($bldQuery);
        $statement->execute();
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Event record update successful.";
      }
      catch(PDOException $exception)
      {
        echo "Unable to update event.";
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Record update failed: " . $exception->getMessage();
      }
    }
  }
  return $r_val;
}

/* This function accepts two optional arguments: full length month name and a
 * four digit year.  If no arguments are passed the function assumes the current
 * month of the current year.  It returns all events in the database that fall
 * under the specified date range.
 */
function getMonthEvents($month = NULL, $year = NULL)
{
  $goodData = 0;
  date_default_timezone_set('US/Mountain');
  if(is_null($month) && is_null($year))
  {
    $currentDate = getdate(time());
    $currentMonth = $currentDate['month'];
    $currentYear = $currentDate['year'];
    $goodData = 1;
  }
  elseif(is_null($month) && !is_null($year))
  {
    $currentDate = getdate(time());
    $currentMonth= $currentDate['month'];
    $currentYear = $year;
    $goodData = 1;
  }
  elseif(!is_null($month) && is_null($year))
  {
    $currentDate = getdate(time());
    $currentYear = $currentDate['year'];
    $currentMonth = $month;
    $goodData = 1;
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Error determining month to be used.";
  }
  
  if($goodData == 1)
  {
    try
    {
      $rangeStart = new DateTime("first day of $currentMonth $currentYear");
      $rangeEnd = new DateTime("last day of $currentMonth $currentYear");
      $timeStampStart = $rangeStart->getTimestamp();
      $timeStampEnd = ($rangeEnd->getTimestamp()) + 86400;
      $bldQuery = "SELECT * FROM events WHERE start >= '$timeStampStart' AND start < '$timeStampEnd'";
      $dbLink = dbconnect();
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchAll(PDO::FETCH_OBJ);
      if(!$result)
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "No events located matching date range provided.";
      }
      else
      {
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Event data located and retrieved.";
        foreach($result as $indEvent)
        {
          if(!$indEvent->rid)
            $indEvent->recurring = "false";
          else
            $indEvent->recurring = "true";
          if(!$indEvent->end)
            $indEvent->allDay = true;
          else
            $indEvent->allDay = false;
        }
        $r_val['DATA'] = $result;
      }
    }
    catch(PDOException $exception)
    {
      echo "Unable to retrieve requested data.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = $exception->getMessage();
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Uanble to create a good date range for event retrieval.";
  }
  return $r_val;
}

/* This function accepts a position title and returns the ID for that title.
 */
function getPositionID($assignment)
{
  if(isset($assignment))
  {
    try
    {
      $dbLink = dbconnect();
      $bldQuery = "SELECT id FROM positions WHERE assignment='$assignment';";
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchObject();
      if(!$result)
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Label name not found in database.";
      }
      else
      {
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Label name found in database.";
        $r_val['DATA'] = $result->id;
      }
    }
    catch(PDOException $exception)
    {
      echo "Unable to retrieve requested data.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Record retrieval failed: " . $exception->getMessage();
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "No assignment passed.";
  }
  return $r_val;
}

/* This function accepts an abbreviated month name (e.g. JAN, FEB) and converts it
 * to a digit which it then returns.
 */
function monthToDigit($monthAbbrev)
{
  date_default_timezone_set('US/Mountain');
  $monthDigit = 0;
  $monthLower = strtolower($monthAbbrev);
  $monthCorrected = ucfirst($monthLower);
  for($monthNum = 1; $monthNum <= 12; $monthNum++)
  {
    if(date("M", mktime(0, 0, 0, $monthNum, 1, 0)) == $monthCorrected)
    {
      $monthDigit = $monthNum;
    }
  }
  return $monthDigit;
}

/* This function accepts a string and a number of characters as arguments.  The
 * function then truncates the string to match the number of characters that are
 * provided.  The function returns the truncated string.
 */
function truncateString($textString, $numChars)
{
  if(strlen($textString) > $numChars)
  {
    $truncatedString = substr($textString, 0, $numChars);
  }
  else
  {
    $truncatedString = $textString;  
  }
  return $truncatedString;
}

?>
