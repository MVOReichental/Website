import $ from "jquery";
import "bootstrap-notify";

$(function () {
    $(".messages-hide-button").on("click", function () {
        var modal = $("#messages-hide-modal");

        modal.data("id", $(this).closest(".messages-list").data("id"));
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
                    icon: "fas fa-exclamation-triangle",
                    message: "Beim Ausblenden ist ein Fehler aufgetreten!"
                }, {
                    type: "danger"
                });
            }
        });
    });

    $(".message-recipient-toggle").on("click", function () {
        $(this).hide();
        $(this).parent().find(".message-recipient").removeClass("message-recipient-limited");
    });
});