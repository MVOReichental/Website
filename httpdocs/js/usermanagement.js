$(function () {
    $("#usermanagement-edit-form").on("submit", function (event) {
        var element = $("#usermanagement-edit-username");
        if (!element.val()) {
            var divElement = element.parent(".form-group");

            divElement.addClass("has-error");
            divElement.find(".help-block").text("Ein Benutzername ist erforderlich!");

            event.preventDefault();
        }
    });

    $("#usermanagement-edit-username-from-name").on("click", function () {
        $("#usermanagement-edit-username").val($("#usermanagement-edit-firstname").val() + " " + $("#usermanagement-edit-lastname").val());
    });

    $("#usermanagement-edit-send-credentials").on("change", function () {
        if (!$(this).is(":checked")) {
            return;
        }

        $("#usermanagement-send-credentials-modal").modal("show");
    });
});