<?php

/*
 * A list of allowed calls
 */
$functions = Array('getroster','getevents');

/*
 * We only allow this method
 */
$method = 'POST';

/*
 * Sample data
 */
$rosterdata = json_decode('[{
    "gname": "Jared", 
    "sname": "Meeker", 
    "addr": "Some Street, Some City, 76493", 
    "hphon": "801-222-1111", 
    "mphon": "801-333-4444",
    "wphon": "801-444-5555",
    "hemail": "jared@somedomain.com",
    "wemail": "jared_meeker@something.org"
}]') or die('cannot decode rosterdata');
$events = json_decode('[{
    "title": "All Day Event",
    "start": "1359035746"
}]') or die('cannot decode events');

/*
 * Generic error generator
 */
function genErr($msg) {
    $error = Array();
    $error['status'] = 'failed';
    $error['msg'] = $msg;
    return json_encode($error);
}

/*
 * Test request method
 */
if ($_SERVER['REQUEST_METHOD'] != $method) {
    die(genErr("Cannot process request."));
}

/*
 * Import request variables
 */
extract($_POST, EXTR_PREFIX_ALL, 'REQ_');

/*
 * Die if the function isn't specified or isn't one of our accepted ones
 */
if (!isset($REQ__func) || !in_array($REQ__func,$functions)) {
    die(genErr("Unknown request type."));
}

/*
 * Execute the function and pass args
 */
$reply = $REQ__func($REQ__args);


/*
 * Available functions
 */

// Returns roster data as JSON
//    TODO: only send data if authenticated
function getroster($args = null) {
    global $rosterdata;
    return json_encode($rosterdata);
}

// Returns event data as JSON
//    TODO: only send data if authenticated
function getevents($args = null) {
    global $events;
    return json_encode($events);
}

/*
 * Reply to AJAX request
 */
echo $reply;
?>
