import $ from "jquery";

$(function () {
    $(".usermanagement-switch-user-button").on("click", function () {
        $("#usermanagement-switch-user-confirm").attr("href", "internal/switch-user/" + $(this).data("userid"));
        $("#usermanagement-switch-user-modal").modal("show");
    });
});