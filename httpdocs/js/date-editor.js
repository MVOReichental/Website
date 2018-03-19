$(function () {
    var table = $("#dates-table");

    table.find("button.remove").on("click", function () {
        $("#dates-remove-title").text($(this).closest("tr").find(".dates-title").text() + " (" + $(this).closest("tr").find(".dates-date").text() + ")");
        $("#dates-remove-modal").modal("show").data("id", $(this).closest("tr").data("id"));
    });

    var datesAutocompletion = null;

    $("#dates-edit-title").typeahead({
        source: function (query, process) {
            if (datesAutocompletion === null) {
                datesAutocompletion = [];
                $.ajax({
                    url: "internal/dates/autocompletion",
                    method: "GET",
                    dataType: "json",
                    success: function (data) {
                        datesAutocompletion = data;
                        process(datesAutocompletion);
                    }
                });
            } else {
                process(datesAutocompletion);
            }
        }
    });

    $("#dates-remove-confirm").on("click", function () {
        $.ajax({
            url: "internal/dates/" + $("#dates-remove-modal").data("id"),
            method: "DELETE",
            success: function () {
                document.location.reload(true);
            },
            error: function () {
                $.notify({
                    icon: "fas fa-exclamation-triangle",
                    message: "Beim Entfernen ist ein Fehler aufgetreten!"
                }, {
                    type: "danger"
                });
            }
        });
    });
});