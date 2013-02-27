<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title></title>
  </head>
  <body>
    <?php
    require_once "functions.php";
    echo "Test Page";
    echo "<br>";
    date_default_timezone_set('US/Mountain');
    $curDate = new DateTime();
    $curTimeStamp = $curDate->getTimestamp();
    
    echo "<pre>";
    print_r($curDate);
    print_r($curTimeStamp);
    echo "<br>";
    $tempVar = truncateString("Jan", "3");
    echo "$tempVar";
    
    ?>
  </body>
</html>
