$(function () {
    var container = $("#roomoccupancyplan-calendar");

    container.fullCalendar({
        locale: "de",
        defaultView: "agendaWeek",
        events: "internal/roomoccupancyplan/entries.json",
        editable: container.data("editable"),
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