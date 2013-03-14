/*
 *
 * Executes after page load (jQuery)
 * 
 */
$(function() {
    // Prep tab data to be jQueryUI-ized
    $( ".tabs" ).tabs({
        activate: function( event, ui ) {
            var tabname = ui.newTab[0].id;
            if (tabname == "calendar") {
                renderCal();
            } else if (tabname == "roster") {
                renderRoster();
            }
        }
    }).addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
    
    // Listen for Enter key on position input
    $("#position").keypress(function(event) {
        if ( event.which == 13 ) {
            event.preventDefault();
            $("#submitpos").click();
        }
    });
    
    // Hide message divs (we'll show them later)
    //$(".error").hide();
    $(".message").hide();
    loadPositions();
});





/*
 *
 * Functions
 * 
 */
function submitAJAX(func,jsondata,callback) {
    //consoleLog("raw json request data ("+func+"): "+JSON.stringify(jsondata));
    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
            func: func, 
            args: jsondata
        }
    }).done(function(msg) {
        //consoleLog("raw ajax reply: "+msg);
        if (isJSON(msg)) {
            if (testResult(msg)) {
                if (callback != null) {
                    callback(jQuery.parseJSON(msg));
                } else {
                    return msg;
                }
            }
        } else {
            showMsg("Unable to understand server reply.", true);
            return;
        }        
    });
}

// Test AJAX result for valid JSON
function isJSON(jsonString) {
    // Do we even need this test?
    if (jsonString === null) return false;
    
    try {
        var a = JSON.parse(jsonString);
        return true;
    } catch(e) {
        return false;
    }
}

// Test an AJAX result for an error
function testResult(jsObj) {
    var result = false;
    var obj = JSON.parse(jsObj);
    
    if (parseInt(obj.RSLT) == 0) result = true;
    
    if (result == false) {
        showMsg(obj.MSSG, true);
    } else {
        showMsg("Server request was successful");
    }
    return result;
}

// Log to console (wrapper)
function consoleLog(msg) {
    if (typeof console.log != "undefined") {
        console.log(newclass + ": " + msg);
    }
}

// Show messages
function showMsg(message,error) {
    if (typeof error == "undefined") error = false;
    
    if (error == true) newclass = "error";
    else newclass = "message";
    
    //consoleLog(newclass + ": " + message);
    
    $("#msg").stop(true,true).text(message).addClass(newclass).fadeIn("fast", function() {
        // Flash once then stay visible for 5 seconds
        $(this).fadeOut("slow").fadeIn("slow").delay(5000).fadeOut("fast", function() {
            $(this).text("").removeClass();
        });     
    })
}





/*
 *
 * CALENDAR
 * 
 */
// Generate fullCalendar object inside #tcal
function renderCal() {
    $('#tcal').empty();
    $('#tcal').fullCalendar({
        // options
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        selectable: true,
        selectHelper: true,
        select: function(start, end, allDay) {
            var title = prompt('Event Title:');
            if (title) {
                $('#tcal').fullCalendar('renderEvent',
                {
                    title: title,
                    start: start,
                    end: end,
                    allDay: allDay
                },
                true // make the event "stick"
                );
            }
            $('#tcal').fullCalendar('unselect');
        },
        editable: true,
        defaultView: 'month',
        eventSources: [{
            url: 'ajax.php',
            type: 'POST',
            data: {
                func: 'getevents',
                args: ''
            },
            error: function() {
                showMsg("There was an error while fetching events!", true);
            },
            color: 'green',   // a non-ajax option
            textColor: 'white' // a non-ajax option
        }],
        eventRender: function(event, element) {
            var startdatestamp = event.start.valueOf()/1000;
            
            var tmpEvent = {
                "id": event.id,
                "title": event.title,
                "start": startdatestamp,
                "category": 1 // faked category for now
            };
            
            if (typeof event.end == null) {
                var enddatestamp = event.end.valueOf()/1000;
                tmpEvent.end = enddatestamp;
            }
            
            if (typeof event.eid == "undefined") {
                saveEvent(tmpEvent);
            } else {
                tmpEvent.eid = event.eid;
                updateEvent(tmpEvent);
            }
        }
    });
}

// Saves a calendar event to the DB
function saveEvent(eventObj) {
    var params = {
        "event": eventObj
    };
    submitAJAX("newevent",params,showEventResult);
}

// Updates a calendar event in the DB
function updateEvent(eventObj) {
    var params = {
        "event": eventObj
    };
    submitAJAX("modevent",params,showEventResult);
}

// Show result of event save
function showEventResult(jsonres) {
    //console.log(JSON.stringify(jsonres));
}

function loadEvents() {
    var params = {}
    submitAJAX("feetchevents",params,showEventResult);
}

// Submits login authentication request
function login() {
    
}

// Initiates AJAX call to get event data, uses refreshevents() as callback
function loadevents() {
    submitAJAX("getevents",null,refreshevents);
}





/*
 *
 * ANNOUNCEMENTS
 * 
 */
// Takes JSON announcement data and populates announcements
function loadannouncements() {
    
}

// Submits new announcement (then re-fetch announcements)
function addannouncement() {
    
}

// Submits announcement change (then re-fetch announcements)
function editannounement() {
    
}





/*
 *
 * ROSTER
 * 
 */
// Takes JSON roster data and populates roster
function buildroster(jsondata) {
    $('#rfamily_container').empty();
    $.each(jsondata, function() {
        var fam = document.createElement("div")
        $(fam).addClass("container_16 rfamily");  
        $.each(this, function(field,value) {
            var part = document.createElement("div");
            $(part).addClass("grid_2 ellipse " + field);
            $(part).html(value);
            $(fam).append($(part).clone());
        });
        $('#rfamily_container').append($(fam).clone());
    });
}

// Initiates AJAX call to get roster data, uses buildroster() as callback
function renderRoster() {
    submitAJAX("getroster",null,buildroster);
}

// Submits roster change(s) (then re-fetches roster)
function updateroster() {
    
}







/*
 *
 * POSITIONS
 * 
 */
// Submits a new position
function addPosition() {
    var newposition = $("#position").val();
    var params = {
        "position": newposition
    };
    submitAJAX("newpos",params,showPosResult);
}

// Render result of addPosition()
function showPosResult(jsonres) {
    var stringres = JSON.stringify(jsonres);
    showMsg("Position successfully saved.",false);
    $("#position").val("");
    loadPositions();
}

// Get all positions
function loadPositions() {
    //    consoleLog("loadpositions: loading positions");
    submitAJAX("getpositions",null,showPositions);
}

// Render positions
function showPositions(jsonres) {
    //    consoleLog("showpositions: " + JSON.stringify(jsonres));
    var positionsdata = jsonres.DATA;
    
    var $positionsDiv = $("#positions");
    $positionsDiv.empty();
    for (var i=0; i < positionsdata.length; i++) {
        $positionsDiv.append(positionsdata[i].assignment).append("<br>");
    }
}





/*
 *
 * MEMBERS
 * 
 */
// Submits a new member
function addMember() {
    var newposition = $("#member").val();
    var params = {
        "member": newposition
    };
    submitAJAX("newmem",params,showMemresult);
}

// Render result of addPosition()
function showMemResult(jsonres) {
    var stringres = JSON.stringify(jsonres);
    showMsg("Member successfully saved.",false);
    $("#member").val("");
    loadMembers();
}

// Get all members
function loadMembers() {
    //    consoleLog("loadpositions: loading positions");
    submitAJAX("getmembers",null,showMembers);
}

// Render positions
function showMembers(jsonres) {
    //    consoleLog("showpositions: " + JSON.stringify(jsonres));
    var membersdata = jsonres.DATA;
    
    var $membersDiv = $("#members");
    $membersDiv.empty();
    for (var i=0; i < membersdata.length; i++) {
        $membersDiv.append(membersdata[i].givenname).append("<br>");
    }
}