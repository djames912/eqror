/*
 * Executes after page load (jQuery)
 */
$(function() {
    // Prep tab data to be jQueryUI-ized
    $( ".tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
              
    // Set active tab to tab #4
    var tabs = $( ".tabs" ).tabs();
    $(".tabs").tabs({
        active: 3
    });
    
    // Generate fullCalendar object inside #tcal
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
        defaultView: 'month'
    });
    $(".tabs").tabs({
        active: 0
    });
    
    // Fetch roster and events via AJAX
    loadroster();
    loadevents();
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

// Takes JSON event data and populates calendar
function refreshevents(jsondata) {
//    $('#tcal').fullCalendar({
//        events: jsondata    
//    });
    //$('#tcal').fullCalendar( 'rerenderEvents' );
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
function loadroster() {
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