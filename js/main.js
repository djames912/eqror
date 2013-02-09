/*
 * Executes after page load (jQuery)
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
 * Functions
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
    }
    return result;
}

// Log to console (wrapper)
function consoleLog(msg) {
    if (typeof console.log != "undefined") {
        console.log(newclass + ": " + msg);
    }
}

// Show error
function showMsg(message,error) {
    if (typeof error == "undefined") error = false;
    
    if (error == true) newclass = "error";
    else newclass = "message";
    
    consoleLog(newclass + ": " + message);
    
    $("#msg").stop(true,true).text(message).addClass(newclass).fadeIn("fast", function() {
        // Flash once then stay visible for 5 seconds
        $(this).fadeOut("slow").fadeIn("slow").delay(5000).fadeOut("fast", function() {
            $(this).text("").removeClass(newclass);
        });     
    })
}

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
        }]
    });
}

// Takes JSON announcement data and populates announcements
function loadannouncements() {
    
}

// Submits new announcement (then re-fetch announcements)
function addannouncement() {
    
}

// Submits announcement change (then re-fetch announcements)
function editannounement() {
    
}

// Initiates AJAX call to get roster data, uses buildroster() as callback
function renderRoster() {
    submitAJAX("getroster",null,buildroster);
}

// Initiates AJAX call to get event data, uses refreshevents() as callback
function loadevents() {
    submitAJAX("getevents",null,refreshevents);
}

// Submits roster change(s) (then re-fetches roster)
function updateroster() {
    
}

// Submits login authentication request
function login() {
    
}

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
    var positionsdata = jsonres.MSSG;
    
    var $positionsDiv = $("#positions");
    $positionsDiv.empty();
    for (var i=0; i < positionsdata.length; i++) {
        $positionsDiv.append(positionsdata[i].assignment).append("<br>");
    }
}