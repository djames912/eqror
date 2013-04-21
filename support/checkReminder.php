#!/usr/bin/php
<?php
/*
 * This file should be copied to /usr/local/sbin and then have permissions 750 set.
 * A crontab entry should be made to run this file at least every 30 minutes.  Make
 * sure you match the run time with the minimum value that is allowed when setting up
 * the events themselves.
 * NOTE: You must adjust the path to the functions.php file or this script will NOT
 * work!.
 * 
 * Also of note, this script currently only checks for subscribers by event type
 * it will eventually support individual subscriber checks as well.
 */
require_once '../includes/functions.php';
$currentDate = array();
$tempDate = new DateTime('NOW');
$currentTimeStamp = $tempDate->getTimestamp();
$tsMax = ($maxDays * 86400) + $currentTimeStamp;
$tempEvents = getEventsByRange($currentTimeStamp, $tsMax);
$eventList = $tempEvents['DATA'];
echo "\n";
echo "Current Time Stamp: $currentTimeStamp";
echo "\n";
foreach($eventList as $indEvent)
{
  echo "Checking Event: " . $indEvent->title;
  echo "\n";
  $tempSubscribers = getEventTypeSubscribers($indEvent->category);
  $eventSubscribers = $tempSubscribers['DATA'];
  foreach($eventSubscribers as $indSubscriber)
  {
    echo "Working on UID: " . $indSubscriber->uid;
    echo "\n";
    $tempMemberData = getMemberNames($indSubscriber->uid);
    $memberNameData = $tempMemberData['DATA'];
    $memberName = $memberNameData->givenname . " " . $memberNameData->surname;
    echo "Member Name: " . $memberName;
    echo "\n";
    $tempReminders = getReminders($indSubscriber->rid);
    $reminderList = $tempReminders['DATA'];
    $convertedTime = date('l F j, Y: g:i a', $indEvent->start);
    foreach($reminderList as $indReminder)
    {
      echo "Current Time Stamp: $currentTimeStamp";
      echo "\n";
      echo "Event Time Stamp: " . $indEvent->start;
      echo "\n";
      echo "Event Date/Time: " . $convertedTime;
      echo "\n";
      echo "Reminder Description: " . $indReminder->description;
      echo "\n";
      echo "Reminder TS Value: " . $indReminder->ts_value;
      echo "\n";
      $testValue = ($indEvent->start - $indReminder->ts_value);
      echo "Test Value: " . $testValue;
      echo "\n";
      if(($indEvent->start - $indReminder->ts_value) >= ($currentTimeStamp - $timeStampVariance) && ($indEvent->start - $indReminder->ts_value) <= ($currentTimeStamp + $timeStampVariance))
      {
        echo "Reminder Triggered.";
        echo "\n";
        $tempData = getEmailAddress($indSubscriber->uid);
        if($tempData['RSLT'] == "1")
        {
          echo $tempData['MSSG'];
          echo "\n";
        }
        else
        {  
          $emailArray = $tempData['DATA'];
          foreach($emailArray as $emailAddress)
          {
            echo "Sending to: $emailAddress->emailaddr";
            echo "\n";
            $mailRecipient = $memberName . " " . '<' . $emailAddress->emailaddr . '>';
            $mailSubject = 'Reminder: ' . $indEvent->title;
            $mailMessage = 'Hello ' . $memberName . ',' . "\r\n" .
                'This an automated reminder ' . $indReminder->description .
                ' in advance of ' . $indEvent->title . '.' . "\r\n" .
                'Scheduled on: ' . $convertedTime . "\r\n\r\n" .
                'Thank you.' . "\r\n" . 'EQRoR Program.' . "\r\n\r\n" .
                'Please DO NOT reply to this reminder, your reply will bounce.';
            $mailHeaders = 'From: eqror@weirdwares.net' . "\r\n" .
                'Reply-To: eqror@weirdwares.net' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            echo "$mailRecipient";
            echo "\n";
            echo "$mailSubject";
            echo "\n";
            echo "$mailHeaders";
            echo "\n";
            echo "$mailMessage";
            echo "\n\n";
            mail($mailRecipient, $mailSubject, $mailMessage, $mailHeaders);
          }
        }
      }
      elseif($currentTimeStamp > ($indEvent->start - $indReminder->ts_value))
      {
        echo "Reminder Time Passed.";
        echo "\n";
      }
      elseif($currentTimeStamp < ($indEvent->start - $indReminder->ts_value))
      {
        echo "Too early for reminder.";
        echo "\n";
      }
      else
      {
        echo "Couldn't determine reminder time.";
        echo "\n";
      }
    }
  }
  echo "##########";
  echo "\n";
}
?>
