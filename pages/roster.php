<?php
/*
 * This file is nested in a container_16
 */
  require_once 'includes/functions.php';
  $rosterData = getTableContents('members');
  $runRosterLoop = $rosterData['RSLT']
?>
<h2>Roster</h2>
<div class="container_16 rfamily_header">
    <div class="grid_2 gname">First</div>
    <div class="grid_2 sname">Last</div>
    <div class="grid_2 addr">Address</div>
    <div class="grid_2 hphon">Home Phone</div>
    <div class="grid_2 mphon">Mobile Phone</div>
    <div class="grid_2 wphon">Work Phone</div>
    <div class="grid_2 pemal">Preferred Email</div>
</div>
<div class="container_16" id="rfamily_container">
  <?php
    //echo "<pre>";
    //print_r($rosterData);
    $rosterList = $rosterData['DATA'];
    foreach($rosterList as $indData)
    {
      $tmpList = getEmailAddress($indData->uid, '1');
      $emailList = $tmpList['DATA'];
      //echo "<pre>";
      //print_r($emailList);
      echo '<div class="grid_2 gname">' . $indData->givenname . '</div>';
      echo '<div class="grid_2 sname">' . $indData->surname . '</div>';
      echo '<div class="grid_2 addr">Not Available</div>';
      echo '<div class="grid_2 hphon">Not AVailable</div>';
      echo '<div class="grid_2 mphon">Not Available</div>';
      echo '<div class="grid_2 wphon">Not Available</div>';
      foreach($emailList as $indEmailAddress)
      {
        echo '<div class="grid_2 pemal">' . trim("$indEmailAddress->emailaddr") . '</div>';
      }
      echo '<BR>';
    }
  ?>
</div>