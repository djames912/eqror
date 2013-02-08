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
    $newPos = "President";
    echo "Position: " . $newPos;
    echo "<br>";
    $tstVar = addPosition($newPos);
    echo "<pre>";
    print_r($tstVar);
    echo "<br>";
    $tstVar = getTableContents(positions);
    print_r($tstVar);
    ?>
  </body>
</html>
