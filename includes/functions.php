<?php
/* Require the connectdb.php file since it contains the necessary information to
 * make a connection to the database.
 */

// This line brings in dbconnect() with local configuration settings:
require_once 'dbconfig.php.local';
require_once 'configure.php';

/* This is a generic function that pulls the entire contents of a table.  It accepts
 * as an argument the name of the table and returns an associative array of the
 * contents of the table.  It's meant to be used as a testing/debugging tool.
 */
function getTableContents($tableName)
{
  try
  {
    $dbLink = dbconnect();
    if($tableName == "members")
    {
      $bldQuery = "SELECT * FROM members ORDER BY surname ASC;";
    }
    else
    {
      $bldQuery = "SELECT * FROM $tableName;";
    }
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $r_val['RSLT'] = "0";
    $r_val['MSSG'] = "Data located in $tableName";
    $r_val['DATA'] = $statement->fetchAll(PDO::FETCH_OBJ);
  }
  catch(PDOException $exception)
  {
    echo "Oooops.  Unable to get your data.";
    $r_val['RSLT'] =  "1"; 
    $r_val['MSSG'] = $exception->getMessage();
  }
  return $r_val;
}

/* This function gets the information regarding the names and types of the columns
 * in a given table.  It expects a table name as an argument and returns an associative
 * array using the column name as the key and the type as the value.
 */
function getTableInfo($tableName)
{
  $fieldData = array();
  if(isset($tableName))
  {
    try
    {
      $dbLink = dbconnect();
      $bldQuery = "DESCRIBE $tableName;";
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchAll(PDO::FETCH_OBJ);
      foreach($result as $indColumn)
      {
        $fieldData[$indColumn->Field] = $indColumn->Type;
      }
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Details for table $tableName";
      $r_val['DATA'] = $fieldData;
    }
    catch(PDOException $exception)
    {
	  try
	  {
		  $dbLink = dbconnect();
		  $bldQuery = "PRAGMA table_info(" . $tableName . ")";
		  $statement = $dbLink->prepare($bldQuery);
		  $statement->execute();
		  $result = $statement->fetchAll(PDO::FETCH_OBJ);
		  foreach($result as $indColumn)
		  {
			$fieldData[$indColumn->name] = $indColumn->type;
		  }
		  $r_val['RSLT'] = "0";
		  $r_val['MSSG'] = "Details for table $tableName";
		  $r_val['DATA'] = $fieldData;
	  }
	  catch(PDOException $exception)
	  {
		  echo "Oooops.  Unable to retrieve your data.";
		  $r_val['RSLT'] =  "1"; 
		  $r_val['MSSG'] = $exception->getMessage();
      }
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
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
    if(is_null($preferred))
      $preferred = $givenName;
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

/* This function accepts a UID as an argument and returns an object containing all
 * of the name data stored in the members table.
 */
function getMemberNames($uid)
{
  if(isset($uid))
  {
    try
    {
      $bldQuery = "SELECT * FROM members WHERE uid='$uid';";
      $dbLink = dbconnect();
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchObject();
      if(!$result)
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "No member data found matching UID: $uid.";
      }
      else
      {
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Data mound matching UID: $uid.";
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
    $r_val['MSSG'] = "UID expected but not received.";
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
      if(!isset($newEvent->rid))
        $newEvent->rid = "0";
      if(!isset($newEvent->details))
        $newEvent->details = "--None provided.--";
      try
      {
        $bldQuery = "INSERT INTO events(rid, title, start, end, category, details)
          VALUES('$newEvent->rid', '$newEvent->title', '$newEvent->start', '$newEvent->end', 
            '$newEvent->category', '$newEvent->details');";
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

/* This function accepts an event object as an argument and then deletes the
 * event matching the provided EID.  It returns whether or not the delete was
 * successful.  If successful the number of rows removed from the database.  If
 * unsuccessful the EID of the event passed in.
 */
function removeEvent($targetEvent)
{
  if(!is_object($targetEvent))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Object expected.  Something else was passed.";
  }
  else
  {
    if(!isset($targetEvent->eid))
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Missing EID.  Unable to continue.";
    }
    else
    {
      try
      {
        if(!isset($targetEvent->end))
          $targetEvent->end = "0";
        $bldQuery = "DELETE FROM events WHERE eid='$targetEvent->eid';";
        $dbLink = dbconnect();
        $statement = $dbLink->prepare($bldQuery);
        $statement->execute();
        $affected_rows = $statement->rowCount();
        if($affected_rows != 0)
        {
          $r_val['RSLT'] = "0";
          $r_val['MSSG'] = "Event(s) successfully deleted.";
          $r_val['DATA'] = $affected_rows;
        }
        else
        {
          $r_val['RSLT'] = "1";
          $r_val['MSSG'] = "Unable to remove requested event.";
          $r_val['DATA'] = $targetEvent->eid;
        }
      }
      catch(PDOException $exception)
      {
        echo "Unable to delete event.";
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "Record delete failed: " . $exception->getMessage();
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
        $updatedEvent->end = "0";
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
  //date_default_timezone_set('US/Mountain');
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
          //if(!$indEvent->rid)
          //  $indEvent->id = "0";
          //else
          //  $indEvent->id = $indEvent->rid;
          $indEvent->id = $indEvent->eid;
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

/* This function accepts a position ID as an argument and returns an array of UIDs
 * that currently have that position assigned to them.
 */
function getPositionHolder($pid)
{
  if(isset($pid))
  {
    $bldQuery = "SELECT uid FROM posholders WHERE pid='$pid';";
    $dbLink = dbconnect();
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "No position holder found matching PID: $pid";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Position holder(s) matching PID: $pid found.";
      $r_val['DATA'] = $result;
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "No position ID passed.";
  }
  return $r_val;
}

/* This function accepts a position ID as an argument and returns the title of the
 * position.
 */
function getPositionName($pid)
{
  if(isset($pid))
  {
    $bldQuery = "SELECT assignment FROM positions WHERE id='$pid';";
    $dbLink = dbconnect();
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $result = $statement->fetchObject();
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Position ID not found in database.";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Position ID found in database.";
      $r_val['DATA'] = $result->assignment;
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "No position ID passed.";
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

/* This function adds a reminder period to the reminders table.  It accepts as arguments
 * a description, a period of time and a type.  The type must be designated in minutes,
 * hours or days.  If no type is designated then the the default of hours will be used.
 * It returns whether or not the reminder was successfully added to the database.
 */
function addReminder($description, $period, $type = NULL)
{
  $multiplier = 0;
  if(!(isset($description) || isset($period)))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    if(is_null($type))
      $multiplier = 3600;
    elseif($type == "minutes")
      $multiplier = 60;
    elseif($type == "hours")
      $multiplier = 3600;
    else
      $multiplier = 86400;
    $insertValue = ($period * $multiplier);
    try
    {
      $bldQuery = "INSERT INTO reminders(ts_value, description) VALUES('$insertValue', '$description');";
      $dbLink = dbconnect();
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Insert into database successful.";
    }
    catch(PDOException $exception)
    {
      echo "Unable to insert data into the database.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Record insert failed: " . $exception->getMessage();
    }
  }
  return $r_val;
}

/* This function adds data to the indsubscribers table.  It accepts an event ID,
 * a user ID and a reminder ID as arguments.  It returns whether or not the database
 * insert was successful or nt.
 */
function addIndividualSubscriber($eid, $uid, $rid)
{
  if(!(isset($eid) || isset($uid) || isset($rid)))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    try
    {
      $bldQuery = "INSERT INTO indsubscribers(eid, uid, rid) VALUES('$eid', '$uid', '$rid');";
      $dbLink = dbconnect();
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Insert into database successful.";
    }
    catch(PDOException $exception)
    {
      echo "Unable to insert data into the database.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Record insert failed: " . $exception->getMessage();
    }
  }
  return $r_val;
}

/* This function accepts and event type ID, a position ID and a reminder ID as arguments
 * and returns whether or not the database insert was successful or not.
 */
function addEventTypeSubscriber($etid, $uid, $rid)
{
  if(!(isset($etid) || isset($uid) || isset($rid)))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    try
    {
      $bldQuery = "INSERT INTO eventtypesubscribers(etid, uid, rid) VALUES('$etid', '$uid', '$rid');";
      $dbLink = dbconnect();
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Insert into database successful.";
    }
    catch(PDOException $exception)
    {
      echo "Unable to insert data into the database.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Record insert failed: " . $exception->getMessage();
    }
  }
  return $r_val;
}

/* This function accepts a UID and an active flag as an argument, and, optionally,
 * a preferred flag.  If active is set to zero, the function will return all email
 * addresses.  If the active flag is set to one (the default) then only active
 * email addresses are returned.
 */
function getEmailAddress($uid, $active, $preferred = NULL)
{
  if(isset($uid) && isset($active))
  {
    if(is_null($preferred) && $active == "0")
      $bldQuery = "SELECT emailaddr FROM email WHERE uid='$uid';";
    elseif(is_null($preferred) && $active == "1")
      $bldQuery = "SELECT emailaddr FROM email WHERE uid='$uid' AND active='1;";
    else
      $bldQuery = "SELECT emailaddr FROM email WHERE uid='$uid' AND active='1' AND preferred='1';";
    try
    {
      $dbLink = dbconnect();
      $statement = $dbLink->prepare($bldQuery);
      $statement->execute();
      $result = $statement->fetchAll(PDO::FETCH_OBJ);
      if(!$result)
      {
        $r_val['RSLT'] = "1";
        $r_val['MSSG'] = "No email address found for UID: $uid";
      }
      else
      {
        $r_val['RSLT'] = "0";
        $r_val['MSSG'] = "Email address(es) for UID: $uid found";
        $r_val['DATA'] = $result;
      }
    }
    catch(PDOException $exception)
    {
      echo "Unable to retrieve data from the database.  Sorry";
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Data retrieval failed: " . $exception->getMessage();
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "UID not passed.  Cannot continue.";
  }
  return $r_val;
}

/* This function accepts two arguments: a timestamp begin range and a timestamp end
 * range.  It returns all events that fall between the timestamp ranges.
 */
function getEventsByRange($timestampMin, $timeStampMax)
{
  if(!(isset($timestampMin) || isset($timeStampMax)))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  else
  {
    $bldQuery = "SELECT * FROM events WHERE start >= '$timestampMin' AND end <= '$timeStampMax';";
    $dbLink = dbconnect();
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "No events found matching provided timestamp range.";
    }
    else
    {
      $r_val['RSLT'] = "0newEmptyPHPWebPage";
      $r_val['MSSG'] = "Events found matching provided timestamp ranges.";
      $r_val['DATA'] = $result;
    }
  }
  return $r_val;
}

/* This function accepts an event type ID and returns the subscribers to that event
 * type.
 */
function getEventTypeSubscribers($etid)
{
  if(!isset($etid))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "No event type ID passed.";
  }
  else
  {
    $bldQuery = "SELECT uid, rid FROM eventtypesubscribers WHERE etid='$etid';";
    $dbLink = dbconnect();
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "No event type subscribers found matching event type ID.";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Subscribers fround for the provided event type.";
      $r_val['DATA'] = $result;
    }
  }
  return $r_val;
}

/* This function accepts a reminder ID as an argument and returns the details of the
 * reminder matching the provided ID.
 */
function getReminders($reminderID)
{
  if(!isset($reminderID))
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "No reminder ID passed.";
  }
  else
  {
    $bldQuery = "SELECT ts_value, description FROM reminders WHERE rid='$reminderID';";
    $dbLink = dbconnect();
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "No reminders found matching reminder ID.";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Reminders found matching the provided reminder ID.";
      $r_val['DATA'] = $result;
    }
  }
  return $r_val;
}

/* This function accepts an event ID as an argument and pulls out all the individuals
 * who have subscribed to that event and returns their UIDs and reminder IDs as an
 * array of objects.
 */
function getIndividualSubscribers($eid)
{
  if(isset($eid))
  {
    $bldQuery = "SELECT uid, rid FROM indsubscribers WHERE eid='$eid';";
    $dbLink = dbconnect();
    $statement = $dbLink->prepare($bldQuery);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    if(!$result)
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "No individual subscribers found matching EID.";
    }
    else
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Individuals who have subscribed to event.";
      $r_val['DATA'] = $result;
    }
  }
  else
  {
    $r_val['RSTL'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  return $r_val;
}

/* This function accepts as arguments a file name, path to the file, the member name, the member
 * email address, the subject of the email and the email message.  It returns whether or not the
 * email was sent.
 */
function sendEmailAttachment($emailFromAddress, $emailReplyToAddress, $fileName, $filePath, $memberName, $memberEmail, $mailSubject, $mailMessage = NULL)
{
  if(is_null($mailMessage))
    $mailMessage = "This space intentionally left blank.\r\n";
  if(isset($fileName) && isset($filePath) && isset($memberName) && isset($memberEmail) && isset($mailSubject))
  {
    $mailRecipient = $memberName . " " . '<' . $memberEmail . '>';
    $fullFile = $filePath . $fileName;
    $fileSize = filesize($fullFile);
    $handle = fopen($fullFile, "r");
    $fileContent = fread($handle, $fileSize);
    fclose($handle);
    $codedContent = chunk_split(base64_encode($fileContent));
    $fid = md5(uniqid(time()));
    $name = basename($fullFile);
    $mailHeaders = 'From: ' . $emailFromAddress . "\r\n";
    $mailHeaders .= 'Reply-To: ' . $emailReplyToAddress . "\r\n";
    $mailHeaders .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $mailHeaders .= "MIME-Version: 1.0\r\n";
    $mailHeaders .= "Content-Type: multipart/mixed; boundary=\"" . $fid . "\"\r\n\r\n";
    $mailHeaders .= "This is a multi-part message in MIME format.\r\n";
    $mailHeaders .= "--" . $fid . "\r\n";
    $mailHeaders .= "Content-type:text/plain; charset=utf-8\r\n";
    $mailHeaders .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $mailHeaders .= $mailMessage . "\r\n";
    $mailHeaders .= "--" . $fid . "\r\n";
    $mailHeaders .= "Content-Type: application/octet-stream; name=\"" . $fileName . "\"\r\n";
    $mailHeaders .= "Content-Transfer-Encoding: 7bit\r\n";
    $mailHeaders .= "Content-Disposition: attachment; filename=\"" . $fileName . "\"\r\n\r\n";
    $mailHeaders .= $codedContent . "\r\n\r\n";
    $mailHeaders .= "--" . $fid . "--";
    if(mail($mailRecipient, $mailSubject, "", $mailHeaders))
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Email message sent.";
    }
    else
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Email message send error.";
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  return $r_val;
}

/* This function accepts a member name, member email address, message subject and
 * a message body as arguments and returns whether or not the email was sent.
 */
function sendEmail($emailFromAddress, $emailReplyToAddress, $memberName, $memberEmail, $mailSubject, $mailMessage = NULL)
{
  if(is_null($mailMessage))
    $mailMessage = "This space intentionally left blank.\r\n";
  if(isset($memberName) && isset($memberEmail) && isset($mailSubject))
  {
    $mailRecipient = $memberName . " " . '<' . $memberEmail . '>';
    $mailHeaders = 'From: ' . $emailFromAddress . "\r\n";
    $mailHeaders .= 'Reply-To: ' . $emailReplyToAddress . "\r\n";
    $mailHeaders .= 'X-Mailer: PHP/' . phpversion();
    if(mail($mailRecipient, $mailSubject, $mailMessage, $mailHeaders))
    {
      $r_val['RSLT'] = "0";
      $r_val['MSSG'] = "Email message sent.";
    }
    else
    {
      $r_val['RSLT'] = "1";
      $r_val['MSSG'] = "Email message send error.";
    }
  }
  else
  {
    $r_val['RSLT'] = "1";
    $r_val['MSSG'] = "Incomplete data set passed.";
  }
  return $r_val;
}
?>
