$(function () {
    var table = $("#dates-table");

    table.find("button.edit").on("click", function () {

    });

    table.find("button.remove").on("click", function () {
        $("#dates-remove-title").text($(this).closest("tr").find(".dates-title").text() + " (" + $(this).closest("tr").find(".dates-date").text() + ")");
        $("#dates-remove-modal").modal("show").data("id", $(this).closest("tr").data("id"));
    });

    $("#dates-remove-confirm").on("click", function () {
        $.ajax({
            url: "internal/dates/" + $("#dates-remove-modal").data("id"),
            method: "DELETE",
            success: function () {
                document.location.reload(true);
            },
            fail: function () {
                $("#dates-remove-error").show().find("span").text("Beim Entfernen ist ein Fehler aufgetreten!");
            }
        });
    });
});