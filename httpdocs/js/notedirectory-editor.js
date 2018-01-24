$(function () {
    $("#notedirectory-editor-titles-table").find("button.remove").on("click", function () {
        var modal = $("#notedirectory-editor-title-remove-modal");
        var tr = $(this).closest("tr");

        modal.find(".entry-title").text(tr.find(".entry-title").text() + " (" + tr.data("id") + ")");
        modal.data("id", $(this).closest("tr").data("id"));
        modal.modal("show");
    });

    $("#notedirectory-editor-title-remove-confirm").on("click", function () {
        $.ajax({
            url: "internal/notedirectory/editor/titles/" + $("#notedirectory-editor-title-remove-modal").data("id"),
            method: "DELETE",
            success: function () {
                document.location.reload(true);
            },
            error: function () {
                $.notify({
                    icon: "fa fa-exclamation-triangle",
                    message: "Beim Entfernen ist ein Fehler aufgetreten!"
                }, {
                    type: "danger"
                });
            }
        });
    });

    $("#notedirectory-editor-programs-table").find("button.remove").on("click", function () {
        var modal = $("#notedirectory-editor-program-remove-modal");
        var tr = $(this).closest("tr");

        modal.find(".entry-title").text(tr.find(".entry-title").text() + " (" + tr.find(".entry-year").text() + ")");
        modal.data("id", $(this).closest("tr").data("id"));
        modal.modal("show");
    });

    $("#notedirectory-editor-program-remove-confirm").on("click", function () {
        $.ajax({
            url: "internal/notedirectory/editor/programs/" + $("#notedirectory-editor-program-remove-modal").data("id"),
            method: "DELETE",
            success: function () {
                document.location.reload(true);
            },
            error: function () {
                $.notify({
                    icon: "fa fa-exclamation-triangle",
                    message: "Beim Entfernen ist ein Fehler aufgetreten!"
                }, {
                    type: "danger"
                });
            }
        });
    });

    $("#notedirectory-editor-program-titles").on("click", "button.remove", function () {
        $(this).closest("tr").remove();
    });

    $("#program-editor-add-title").on("click", function () {
        $("#notedirectory-editor-program-add-title-modal").modal("show");
    });

    var addTitleModal = $("#notedirectory-editor-program-add-title-modal");

    addTitleModal.find(".search").on("keyup", function () {
        var search = $(this).val().toLowerCase().trim();

        addTitleModal.find(".entry").each(function () {
            if ($(this).find(".entry-title").text().toLowerCase().indexOf(search) >= 0) {
                $(this).show();
                return;
            }

            if ($(this).find(".entry-composer").text().toLowerCase().indexOf(search) >= 0) {
                $(this).show();
                return;
            }

            if ($(this).find(".entry-arranger").text().toLowerCase().indexOf(search) >= 0) {
                $(this).show();
                return;
            }

            if ($(this).find(".entry-publisher").text().toLowerCase().indexOf(search) >= 0) {
                $(this).show();
                return;
            }

            $(this).hide();
        });
    });

    addTitleModal.find("button.add").on("click", function () {
        var tr = $(this).closest("tr");

        var titleRow = $("<tr>");

        var numberInput = $("<input>");
        numberInput.attr("type", "number");
        numberInput.attr("name", "title_number[]");
        titleRow.append($("<td>").append(numberInput));

        var idInput = $("<input>");
        idInput.attr("type", "hidden");
        idInput.attr("name", "title_id[]");
        idInput.val(tr.data("id"));
        titleRow.append($("<td>").append(idInput).append($("<span>").text(tr.find(".entry-title").text())));

        titleRow.append($("<td>").text(tr.find(".entry-composer").text()));
        titleRow.append($("<td>").text(tr.find(".entry-arranger").text()));
        titleRow.append($("<td>").text(tr.find(".entry-publisher").text()));

        var removeButton = $("<button>");
        removeButton.addClass("btn btn-xs btn-danger remove");
        removeButton.attr("type", "button");
        removeButton.data("toggle", "tooltip");
        removeButton.attr("title", "L&ouml;schen");
        removeButton.append($("<i>").addClass("fa fa-remove"));
        titleRow.append($("<td>").append(removeButton));

        $("#notedirectory-editor-program-titles").find("tbody").append(titleRow);
    });
});