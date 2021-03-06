/*
 *
 * Executes after page load (jQuery)
 * 
 */
$(function() {
    // Loaded events
    window.events = [];

    // Prep tab data to be jQueryUI-ized
    $(".tabs").tabs({
        activate: function(event, ui) {
            var tabname = ui.newTab[0].id;
            if (tabname == "calendar") {
                renderCal();
            } else if (tabname == "roster") {
                renderRoster();
            }
        }
    }).addClass("ui-tabs-vertical ui-helper-clearfix");
    $(".tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");

    // Listen for Enter key on position input
    $("#position").keypress(function(event) {
        if (event.which == 13) {
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
function submitAJAX(func, jsondata, callback) {
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
            if (func == 'getevents' || testResult(msg)) {
                var msgObj = jQuery.parseJSON(msg);
                if (typeof callback != "undefined") {
                    callback(msgObj);
                } else {
                    return msgObj;
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
    if (jsonString === null)
        return false;

    try {
        var a = JSON.parse(jsonString);
        return true;
    } catch (e) {
        return false;
    }
}

// Test an AJAX result for an error
function testResult(jsObj) {
    var result = false;
    var obj = JSON.parse(jsObj);

    if (parseInt(obj.RSLT) == 0)
        result = true;

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
function showMsg(message, error) {
    if (typeof error == "undefined")
        error = false;

    if (error == true)
        newclass = "error";
    else
        newclass = "message";

    //consoleLog(newclass + ": " + message);

    $("#msg").stop(true, true).text(message).addClass(newclass).fadeIn("fast", function() {
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
// Generate fullCalendar object inside #cal
function renderCal() {
    $('#cal').empty();
    $('#cal').fullCalendar({
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
                var tmpEvent = {
                    "title": title,
                    "start": start,
                    "end": end,
                    "allDay": allDay,
                    "category": 1 // faked category for now
                };
                eventAction("save", tmpEvent);
            }
            $('#cal').fullCalendar('unselect');
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
                color: 'green', // a non-ajax option
                textColor: 'white' // a non-ajax option
            }],
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
            if (typeof event.eid != "undefined") {
                eventAction("update", event);
            }
        },
        eventClick: function(event, jsEvent, view) {
            // only allow editing of events with an eid property (from our db)
            if (typeof event.eid != "undefined") {
                // Was Control key pressed when the event was clicked?  If so, this is a delete.
                if (jsEvent.ctrlKey == true) {
                    var result = confirm('Delete event "' + event.title + '" ?');
                    if (result == true) eventAction("delete", event);
                }
                else {  // No control key pressed, this is an edit.
                    var title = prompt('Event Title:', event.title);
                    if (title != "" && title != null) {   // Capture empty title and cancel button (null)
                        event.title = title;
                        eventAction("update", event);
                    }
                }
            }
        }
    });
}

// Initiates an event action (save, update)
function eventAction(action, event) {
    var startdatestamp = event.start.valueOf() / 1000;
    var enddatestamp = 0;

    if (typeof event.allDay == "undefined" || event.allDay == true)
        enddatestamp = 0;
    else if (typeof event.allDay != "undefined" && event.allDay == false) {
        if (event.end == null)
            enddatestamp = startdatestamp + (7200);   // fullCalendar Defaults to two hours long
        else
            enddatestamp = event.end.valueOf() / 1000;
    }

    var tmpEvent = {
        "title": event.title,
        "start": startdatestamp,
        "end": enddatestamp,
        "category": 1 // faked category for now
    };

    if (typeof event.eid != "undefined")
        tmpEvent.eid = event.eid;

    if (action == "save")
        saveEvent(tmpEvent);
    else if (action == "update")
        updateEvent(tmpEvent);
    else if (action == "delete")
        deleteEvent(tmpEvent);
}

// Saves a calendar event to the DB
function saveEvent(eventObj) {
    var params = {
        "event": eventObj
    };
    submitAJAX("newevent", params, showEventResult);
}

// Updates a calendar event in the DB
function updateEvent(eventObj) {
    var params = {
        "event": eventObj
    };
    submitAJAX("modevent", params, showEventResult);
}

// Deletes a calendar event in the DB
function deleteEvent(eventObj) {
    var params = {
        "event": eventObj
    };
    //console.log('Event deletion is disabled.');
    submitAJAX("delevent", params, showEventResult);
}

// Show result of event save
function showEventResult(jsonres) {
    $("#cal").fullCalendar('removeEvents');
    $("#cal").fullCalendar('refetchEvents');
}

// Submits login authentication request
function login() {

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
        $.each(this, function(field, value) {
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
    submitAJAX("getroster", null, buildroster);
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
    submitAJAX("newpos", params, showPosResult);
}

// Render result of addPosition()
function showPosResult(jsonres) {
    var stringres = JSON.stringify(jsonres);
    showMsg("Position successfully saved.", false);
    $("#position").val("");
    loadPositions();
}

// Get all positions
function loadPositions() {
    //    consoleLog("loadpositions: loading positions");
    submitAJAX("getpositions", null, showPositions);
}

// Render positions
function showPositions(jsonres) {
    //    consoleLog("showpositions: " + JSON.stringify(jsonres));
    var positionsdata = jsonres.DATA;

    var $positionsDiv = $("#positions");
    $positionsDiv.empty();
    for (var i = 0; i < positionsdata.length; i++) {
        $positionsDiv.append(positionsdata[i].assignment).append("<br>");
    }
}

// Fetch form fields
function getFormFields(formname) {
	var ajaxFunc = "";
	var params = {
        "formname": null
    };
	switch(formname) {
		case "positions":
		  params.formname = "position";
		  break;
		case "members":
		  params.formname = "member";
		  break;
		default:
		  return;
	}
	submitAJAX("getform", params, buildForm);
}

// Build form
function buildForm(jsonres) {
	var formfielddata = jsonres.DATA;
	var $dialogDiv = $( "#dialog-form" );
	
	// Loop over form and present add fields
	$dialogDiv.empty();
	for (var prop in formfielddata) {
		var type = formfielddata[prop];
		var $newlabel = $( "<label />" );
		$newlabel.attr('for', prop);
		$newlabel.text(prop);
		var $newfield = $( "<input />" );
		$newfield.attr('type', 'text');
		$newfield.attr('id', prop);
		
		$dialogDiv.append($newlabel);
		$dialogDiv.append($newfield).append('<br />');
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
    submitAJAX("newmem", params, showMemresult);
}

// Render result of addPosition()
function showMemResult(jsonres) {
    var stringres = JSON.stringify(jsonres);
    showMsg("Member successfully saved.", false);
    $("#member").val("");
    loadMembers();
}

// Get all members
function loadMembers() {
    //    consoleLog("loadpositions: loading positions");
    submitAJAX("getmembers", null, showMembers);
}

// Render positions
function showMembers(jsonres) {
    //    consoleLog("showpositions: " + JSON.stringify(jsonres));
    var membersdata = jsonres.DATA;

    var $membersDiv = $("#members");
    $membersDiv.empty();
    for (var i = 0; i < membersdata.length; i++) {
        $membersDiv.append(membersdata[i].givenname).append("<br>");
    }
}
