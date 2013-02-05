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
        <script type='text/javascript' src='js/fullcalendar/fullcalendar.min.js'></script>
        <script type='text/javascript' src='js/main.js'></script>
    </head>
    <body>
        <div class="container_16 ui-corner-top shadow header sitetitle">
            <div class="grid_16 user">user (logout)</div>
            EQ RoR
        </div>
        <div class="container_16 tabs shadow">
            <ul>
                <li id="home"><a href="#thome">Home</a></li>
                <li id="roster"><a href="#troster">Roster</a></li>
                <li id="calendar"><a href="#tcal">Calendar</a></li>
                <li id="admin"><a href="#tadmin">Admin</a></li>
            </ul>
            <div id="thome" class="container_16">
                <?php include 'pages/announcements.php'; ?>
            </div>
            <div id="troster" class="container_16">
                <?php include 'pages/roster.php'; ?>
            </div>
            <div id="tcal" class="container_16"></div>
            <div id="tadmin" class="container_16">
                <?php include 'pages/admin.php'; ?>
            </div>
        </div>
    </body>
</html>
