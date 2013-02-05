<?php
/* Require the connectdb.php file since it contains the necessary information to
 * make a connection to the database.
 */
require_once "connectdb.php";

/* This is a generic function that pulls the entire contents of a table.  It accepts
 * as an argument the name of the table and returns and associative array of the
 * contents of the table.  It's meant to be used as a testing/debugging tool.
 */
function getTableContents($tableName)
{
  $dbLink = dbconnect();
  if(!$dbLink)
    $r_val['ERR'] = "Database connection problem encountered.";
  else
  {
    $dbQuery = "SELECT * from $tableName;";
    $result = mysql_query($dbQuery);
    $rows = mysql_num_rows($result);
    for($loop = 0; $loop < $rows; ++$loop)
    {
      $row[$loop] = mysql_fetch_row($result, MYSQL_ASSOC);
    }
    $r_val = $row;
  }
  return $r_val;
}

/* This function inserts records into the positions table.  It accepts as an
 * argument the name of the position that needs to be created.  It returns
 * whether or not the insert was successful.
 */
function addPosition($position)
{
  $dbLink = dbconnect();
}
?>
