$(function(){
    var table = $("#notedirectory-editor-titles-table");

    table.find("button.remove").on("click", function () {
        var modal = $("#notedirectory-editor-title-remove-modal");

        modal.find(".entry-title").text($(this).closest("tr").find(".entry-title").text() + " (" + $(this).closest("tr").data("id") + ")");
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
});