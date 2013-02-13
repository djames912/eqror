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
    $tmpVar = assignPosition("3", "8");
    echo "<pre>";
    print_r($tmpVar);
    
    ?>
  </body>
</html>
