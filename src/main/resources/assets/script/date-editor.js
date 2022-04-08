import $ from "jquery";
import "bootstrap-3-typeahead";

$(function () {
    var table = $("#dates-table");

    table.find("button.remove").on("click", function () {
        $("#dates-remove-title").text($(this).closest("tr").find(".dates-title").text() + " (" + $(this).closest("tr").find(".dates-date").text() + ")");
        $("#dates-remove-modal").modal("show").data("id", $(this).closest("tr").data("id"));
    });

    var datesAutocompletionData = null;

    $("#dates-edit-form").on("submit", function (event) {
        if ($("#dates-edit-groups :selected").length || $("#dates-edit-public").is(":checked")) {
            return;
        }

        $("#dates-edit-missing-group-info").modal("show");

        event.preventDefault();
    });

    $("#dates-edit-title").typeahead({
        source: function (query, process) {
            loadDateEditorTypeaheadData("titles", process);
        }
    });

    $("#dates-edit-location").typeahead({
        source: function (query, process) {
            loadDateEditorTypeaheadData("locations", process);
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

    function loadDateEditorTypeaheadData(type, callback) {
        if (datesAutocompletionData !== null) {
            if (datesAutocompletionData.hasOwnProperty(type)) {
                callback(datesAutocompletionData[type]);
            }

            return;
        }

        datesAutocompletionData = {};
        $.ajax({
            url: "internal/dates/autocompletion",
            method: "GET",
            dataType: "json",
            success: function (data) {
                datesAutocompletionData = data;

                if (datesAutocompletionData.hasOwnProperty(type)) {
                    callback(datesAutocompletionData[type]);
                }
            }
        });
    }
});