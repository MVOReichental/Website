$(function () {
    $("#roomoccupancyplan-calendar").fullCalendar({
        locale: "de",
        defaultView: "agendaWeek",
        events: "internal/roomoccupancyplan/entries.json",
        editable: true,
        eventDrop: function (event) {
            $.post("internal/roomoccupancyplan/entries/" + event.id, {
                start: event.start.format(),
                end: event.end.format()
            });
        },
        eventResize: function (event) {
            $.post("internal/roomoccupancyplan/entries/" + event.id, {
                start: event.start.format(),
                end: event.end.format()
            });
        }
    });
});