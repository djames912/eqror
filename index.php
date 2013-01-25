<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="js/jquery/css/smoothness/jquery-ui-1.9.2.custom.min.css" />
        <link rel="stylesheet" type="text/css" href="styles/960/css/min/960.css" />
        <link rel="stylesheet" type="text/css" href="styles/960/css/min/text.css" />
        <link rel="stylesheet" type="text/css" href="styles/tabset.css" />
        <script src="js/jquery/js/jquery-1.8.3.js"></script>
        <script src="js/jquery/js/jquery-ui-1.9.2.custom.min.js"></script>
        <link rel='stylesheet' type='text/css' href='js/fullcalendar/fullcalendar.css' />
        <script type='text/javascript' src='js/fullcalendar/fullcalendar.js'></script>
        <script type='text/javascript' src='js/main.js'></script>
    </head>
    <body>
        <div class="container_16 ui-corner-top shadow header sitetitle">
            <div class="grid_16 user">user (logout)</div>
            EQ RoR
        </div>
        <div class="container_16 tabs shadow">
            <ul>
                <li><a href="#thome">Home</a></li>
                <li><a href="#troster">Roster</a></li>
                <li><a href="#t3">Something</a></li>
                <li><a href="#tcal">Calendar</a></li>
            </ul>
            <div id="thome" class="container_16">
                <h2>Announcements</h2>
                <h5>12.05.2013</h5>
                <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. </p>

                <h5>2 Dec 2012</h5>
                <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. </p>
            </div>
            <div id="troster" class="container_16">
                <h2>Roster</h2>
                <div class="container_16 rfamily_header">
                    <div class="grid_2 gname">First</div>
                    <div class="grid_2 sname">Last</div>
                    <div class="grid_2 addr">Address</div>
                    <div class="grid_2 hphon">Home Phone</div>
                    <div class="grid_2 mphon">Mobile Phone</div>
                    <div class="grid_2 wphon">Work Phone</div>
                    <div class="grid_2 hemail">Email (home)</div>
                    <div class="grid_2 wemail">Email (work)</div>
                </div>
                <div class="container_16" id="rfamily_container"></div>
            </div>
            <div id="t3" class="container_16">
                <h2>Something</h2>
                <p></p>
            </div>
            <div id="tcal" class="container_16"></div>
        </div>
    </body>
</html>
