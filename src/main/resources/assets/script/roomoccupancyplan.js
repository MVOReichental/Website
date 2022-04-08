import $ from "jquery";
import "fullcalendar";
import "../style/roomoccupancyplan.scss";

$(function () {
    $("#roomoccupancyplan-calendar").fullCalendar({
        height: "parent",
        locale: "de",
        defaultView: "agendaWeek",
        events: "internal/roomoccupancyplan/entries.json",
        selectHelper: true,
        allDaySlot: false,
        scrollTime: "10:00:00",
        slotDuration: "00:15:00",
        slotLabelFormat: "H:mm"
    });
});