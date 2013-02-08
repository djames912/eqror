<?php
/* This is a simple function to open a connection to the database that is being
 * used.  It is kept as a separate file in an effort to make it a little more
 * secure.  It accepts no arguments and returns the link resource to the 
 * database.
 */
function dbconnect()
{
  $dbcon = new PDO('mysql:host=localhost; dbname=orgror; charset=utf8', 'roruser',
      'ClEAnm3Up!', array(PDO::ATTR_EMULATE_PREPARES => false,
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  return $dbcon;
}
?>
