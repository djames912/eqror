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
});


/*
 * Functions
 */
function submitAJAX(func,jsondata,callback) {
    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
            func: func, 
            args: jsondata
        }
    }).done(function(msg) {
        if (callback != null) {
            callback(jQuery.parseJSON(msg));
        } else {
            return msg;
        }
    });
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

// 
function renderCal() {
    // Generate fullCalendar object inside #tcal
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
                alert('there was an error while fetching events!');
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
    var params = {"position": newposition};
    submitAJAX("newpos",params,showPosResult);
    $("#position").val("");
}

function showPosResult(jsonres) {
    var stringres = JSON.stringify(jsonres);
    $("#posresult").text(stringres);
}
