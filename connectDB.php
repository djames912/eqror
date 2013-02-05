<?php
/* This is a simple function to open a connection to the database that is being
 * used.  It is kept as a separate file in an effort to make it a little more
 * secure.  It accepts no arguments and returns the link resource to the 
 * database.
 */
function dbconnect()
{
  $db_host = "localhost";
  $db_user = "roruser";
  $db_pass = "ClEAnm3Up!";
  $db_name = "orgror";
  $link = mysql_connect("$db_host","$db_user","$db_pass") or die("Conection to database
    failed: " . mysql_error());
  mysql_select_db("$db_name") or die("Unable to select database: " . $db_name . mysql_error($link));
  return $link;
}
?>
