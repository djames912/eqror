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
    $tmpVar = getTypeLabel("addresstypes", "1");
    echo "<pre>";
    print_r($tmpVar);
    //$tmpVar = getMemberUID("Surname", "GivenName");
    //$memberUID = $tmpVar['MSSG'];
    //echo "UID: $memberUID";
    //$newMember = addMember("Surname", "GivenName", "MiddleName");
    //$memberUID = $newMember['MSSG'];
    //echo "Member UID: $memberUID";
    //echo "<pre>";
    //print_r($newMember);
    //$newMember = checkMemberExists("Surname", "GivenName");
    //$newPos = "Other";
    //echo "Telephone Type: " . $newPos;
    //echo "<br>";
    //$tstVar = addType(telecomtypes, $newPos);
    //echo "<pre>";
    //print_r($tstVar);
    //echo "<br>";
    //$contentVar = getTableContents(telecomtypes);
    //print_r($contentVar);
    //$tstVar = getTableContents(positions);
    //print_r($tstVar);
    //$tstVar = checkExists(positions, assignment, President);
    //print_r($tstVar);
    //$myValue = $tstVar['RSLT'];
    //echo "<br>";
    //echo "$myValue";
    ?>
  </body>
</html>
