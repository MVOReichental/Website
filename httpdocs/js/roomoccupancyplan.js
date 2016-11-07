$(function () {
    var container = $("#roomoccupancyplan-calendar");

    container.fullCalendar({
        locale: "de",
        defaultView: "agendaWeek",
        events: "internal/roomoccupancyplan/entries.json",
        editable: container.data("editable"),
        selectable: container.data("editable"),
        selectHelper: true,
        select: function (start, end) {
            var modal = $("#roomoccupancyplan-edit-modal");

            modal.removeData("id");

            $("#roomoccupancyplan-edit-form")[0].reset();
            $("#roomoccupancyplan-edit-date").val(start.format("YYYY-MM-DD"));
            $("#roomoccupancyplan-edit-start-time").val(start.format("HH:mm:ss"));
            $("#roomoccupancyplan-edit-end-time").val(end.format("HH:mm:ss"));

            modal.modal("show");

            container.fullCalendar("unselect");
        },
        eventClick: function (event) {
            if (!container.data("editable")) {
                return;
            }

            var modal = $("#roomoccupancyplan-edit-modal");

            modal.data("id", event.id);

            $("#roomoccupancyplan-edit-form")[0].reset();
            $("#roomoccupancyplan-edit-title").val(event.title);
            $("#roomoccupancyplan-edit-date").val(event.date);
            $("#roomoccupancyplan-edit-start-time").val(event.start.format("HH:mm:ss"));
            $("#roomoccupancyplan-edit-end-time").val(event.end.format("HH:mm:ss"));
            $("#roomoccupancyplan-edit-repeat-weekly").prop("checked", event.repeatWeekly);
            $("#roomoccupancyplan-edit-repeat-till-date").val(event.repeatTillDate === null ? "" : event.repeatTillDate);

            modal.modal("show");
        },
        eventDrop: function (event) {
            $.post("internal/roomoccupancyplan/entries/" + event.id + "/move-resize", {
                start: event.start.format(),
                end: event.end.format()
            });
        },
        eventResize: function (event) {
            $.post("internal/roomoccupancyplan/entries/" + event.id + "/move-resize", {
                start: event.start.format(),
                end: event.end.format()
            });
        }
    });

    $("#roomoccupancyplan-edit-form").on("submit", function (event) {
        event.preventDefault();

        var id = $("#roomoccupancyplan-edit-modal").data("id");
        var url = "internal/roomoccupancyplan/entries";

        if (id !== null) {
            url += "/" + id;
        }

        $.ajax({
            url: url,
            method: "POST",
            data: {
                title: $("#roomoccupancyplan-edit-title").val(),
                date: $("#roomoccupancyplan-edit-date").val(),
                start: $("#roomoccupancyplan-edit-start-time").val(),
                end: $("#roomoccupancyplan-edit-end-time").val(),
                repeatWeekly: $("#roomoccupancyplan-edit-repeat-weekly").is(":checked") ? 1 : 0,
                repeatTillDate: $("#roomoccupancyplan-edit-repeat-till-date").val()
            },
            success: function() {
                container.fullCalendar("refetchEvents");

                $("#roomoccupancyplan-edit-modal").modal("hide");
            }
        });
    });
});