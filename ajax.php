<?php

$method = 'POST';
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

function genErr($msg) {
    $error = Array();
    $error['status'] = 'failed';
    $error['msg'] = $msg;
    return json_encode($error);
}

if ($_SERVER['REQUEST_METHOD'] != $method) {
    die(genErr("Cannot process request."));
}

extract($_POST, EXTR_PREFIX_ALL, 'REQ_');

if (!isset($REQ__func)) {
    die(genErr("Invalid request name: " . $_POST['func']));
}

// Execute our function
$REQ__func($REQ__args);

function getroster($args = null) {
    global $rosterdata;
    echo json_encode($rosterdata);
}

function getevents($args = null) {
    global $events;
    echo json_encode($events);
}

?>
