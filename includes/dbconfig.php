<?php

/*
 * This file needs to be copied into configure.php.local and the correct values
 * set to connect to the database.  This is just an example configuration.
 */

/*
 * This function creates a connection to the database and returns a connection
 * resource.  It accepts no arguments.
 */
function dbconnect()
{
  $dbHost = "localhost";
  $dbName = "changeme";
  $dbUser = "changeme";
  $dbPass = "changeme";
  try
  {
    $connection = "mysql:host=" . $dbHost . "; dbname=" . $dbName;
    $dbcon = new PDO($connection, $dbUser, $dbPass, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    return $dbcon;
  }
  catch (PDOException $exception)
  {
    echo "<p>Unable to connect to the database</p>";
    echo "<p>Please check your database installation/setup and try again.";
    $dbcon['RSLT'] = "1";
    $dbcon['MSSG'] = $exception->getMessage();
  }
}
?>
