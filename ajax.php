<?php

require_once 'functions.php';

/*
 * A list of allowed calls
 */
$functions = Array(
    'getroster', 
    'getevents', 
    'newpos', 
    'getpositions', 
    'getmembers', 
    'getaddresstypes', 
    'getemailtypes', 
    'gettelecomtypes', 
    'newevent', 
    'modevent',
    'delevent');

/*
 * We only allow this method
 */
$method = 'POST';


/*
 * Test request method
 */
if ($_SERVER['REQUEST_METHOD'] != $method) {
    die(genErr("Cannot process request."));
}

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
}]');
$events = json_decode('[
    {
    "title": "All Day Event",
    "start": "1359035746"
    },
    {
    "title": "All Day Event2",
    "start": "1358949346"
    }
]');

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
 * Import request variables
 */
extract($_POST, EXTR_PREFIX_ALL, 'REQ_');

/*
 * Die if the function isn't specified or isn't one of our accepted ones
 */
if (!isset($REQ__func) || !in_array($REQ__func, $functions)) {
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
    $result = getMonthEvents(null, null);
//    foreach ($result["DATA"] as $event) {
//        if ($event->end < 1)
//            $event->allDay = true;
//    }
    return json_encode($result["DATA"]);
}

// Returns result as JSON
//    TODO: send only if authenticated
function newpos($args = null) {
    return json_encode(addPosition($args["position"]));
}

// Returns result as JSON
function getpositions($args = null) {
    $positionsdata = getTableContents("positions");
    return json_encode($positionsdata);
}

// Returns result as JSON
//    TODO: send only if authenticated
function newmem($args = null) {
    return json_encode(addMember($args["surn"], $args["givn"], $args["midl"], $args["sfx"]));
}

// Returns result as JSON
//    TODO: send only if authenticated
function getmembers($args = null) {
    $memberdata = getTableContents("members");
    return json_encode($memberdata);
}

// Returns result as JSON
function getaddresstypes($args = null) {
    $addresstypedata = getTableContents("addresstypes");
    return json_encode($addresstypedata);
}

// Returns result as JSON
function getemailtypes($args = null) {
    $emailtypesdata = getTableContents("addresstypes");
    return json_encode($emailtypesdata);
}

// Returns result as JSON
function gettelecomtypes($args = null) {
    $telecomtypesdata = getTableContents("addresstypes");
    return json_encode($telecomtypesdata);
}

// Save the incoming event
function newevent($args) {
    $eventObj = (object) $args["event"];
    return json_encode(addEvent($eventObj));
}

// Updates the incoming event
function modevent($args) {
    $eventObj = (object) $args["event"];
    return json_encode(updateEvent($eventObj));
}

// Updates the incoming event
function delevent($args) {
    $eventObj = (object) $args["event"];
    return json_encode(removeEvent($eventObj));
}

/*
 * Reply to AJAX request
 */
echo $reply;
?>
