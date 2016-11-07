$(function () {
    $(".messages-hide-button").on("click", function () {
        var modal = $("#messages-hide-modal");

        modal.data("id", $(this).parent(".messages-list").data("id"));
        modal.modal("show");
    });

    $("#messages-hide-confirm-button").on("click", function () {
        $.ajax({
            url: "internal/messages/" + $("#messages-hide-modal").data("id") + "/hide-for-user",
            method: "POST",
            success: function () {
                document.location.reload();
            },
            error: function () {
                $.notify({
                    icon: "fa fa-exclamation-triangle",
                    message: "Beim Ausblenden ist ein Fehler aufgetreten!"
                }, {
                    type: "danger"
                });
            }
        });
    });
});