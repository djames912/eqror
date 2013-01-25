$(function() {
    $( ".tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
                
    var tabs = $( ".tabs" ).tabs();
    $(".tabs").tabs({
        active: 3
    });
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

function refreshevents(jsondata) {
//    $('#tcal').fullCalendar({
//        events: jsondata    
//    });
    //$('#tcal').fullCalendar( 'rerenderEvents' );
}

function loadannouncements() {
    
}

function addannouncement() {
    
}

function editannounement() {
    
}

function loadroster() {
    submitAJAX("getroster",null,buildroster);
}

function loadevents() {
    submitAJAX("getevents",null,refreshevents);
}

function updateroster() {
    
}

function login() {
    
}