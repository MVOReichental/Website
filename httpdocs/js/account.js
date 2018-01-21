$(function () {
    $("#settings-password-form").submit(function (event) {
        if ($("#settings-password-new").val() != $("#settings-password-new-confirm").val()) {
            var box = $("#settings-password-new-box");

            box.addClass("has-error");
            box.find(".help-block").text("Die Passw\u00f6rter stimmen nicht \u00fcberein!");

            event.preventDefault();
        }
    });

    $("#settings-email-form").submit(function (event) {
        if ($("#settings-email-new").val() != $("#settings-email-new-confirm").val()) {
            var box = $("#settings-email-new-box");

            box.addClass("has-error");
            box.find(".help-block").text("Die Email-Adressen stimmen nicht \u00fcberein!");

            event.preventDefault();
        }
    });

    $("#settings-2fa-disable").on("click", function () {
        var passwordFormGroup = $("#settings-2fa-password-group");
        passwordFormGroup.removeClass("has-error");

        var passwordInfoBox = passwordFormGroup.find(".help-block");
        passwordInfoBox.text("");

        var password = $("#settings-2fa-password").val();
        if (password == "") {
            passwordFormGroup.addClass("has-error");
            passwordInfoBox.text("Bitte gebe dein aktuelles Passwort ein!");
            return;
        }

        $.ajax({
            url: "internal/settings/2fa/disable",
            method: "POST",
            data: {
                "password": password
            },
            success: function (uri) {
                document.location.reload();
            },
            error: function (jqXHR) {
                passwordFormGroup.addClass("has-error");

                if (jqXHR.responseText == "INVALID_PASSWORD") {
                    passwordInfoBox.text("Das angegebene Passwort ist ung\u00fcltig!");
                } else {
                    passwordInfoBox.text("Ein unbekannter Fehler ist aufgetreten (Fehler " + jqXHR.status + ")!");
                }
            }
        });
    });

    $("#settings-2fa-enable").on("click", function () {
        var passwordFormGroup = $("#settings-2fa-password-group");
        passwordFormGroup.removeClass("has-error");

        var passwordInfoBox = passwordFormGroup.find(".help-block");
        passwordInfoBox.text("");

        var password = $("#settings-2fa-password").val();
        if (password == "") {
            passwordFormGroup.addClass("has-error");
            passwordInfoBox.text("Bitte gebe dein aktuelles Passwort ein!");
            return;
        }

        $.ajax({
            url: "internal/settings/2fa/request",
            method: "POST",
            data: {
                "password": password
            },
            success: function (data) {
                $("#settings-2fa-qrcode").qrcode(data.uri);
                $("#settings-2fa-secret").val(data.secret);

                $("#settings-2fa-enable-modal").modal("show");
            },
            error: function (jqXHR) {
                passwordFormGroup.addClass("has-error");

                if (jqXHR.responseText == "INVALID_PASSWORD") {
                    passwordInfoBox.text("Das angegebene Passwort ist ung\u00fcltig!");
                } else {
                    passwordInfoBox.text("Ein unbekannter Fehler ist aufgetreten (Fehler " + jqXHR.status + ")!");
                }
            }
        });
    });

    $("#settings-2fa-enable-submit").on("click", function () {
        var tokenFormGroup = $("#settings-2fa-token-group");
        tokenFormGroup.removeClass("has-error");

        var tokenInfoBox = tokenFormGroup.find(".help-block");
        tokenInfoBox.text("");

        var token = $("#settings-2fa-token").val();
        if (token == "") {
            tokenFormGroup.addClass("has-error");
            tokenInfoBox.text("Bitte gebe den Code ein!");
            return;
        }

        $.ajax({
            url: "internal/settings/2fa/enable",
            method: "POST",
            data: {
                "token": token
            },
            success: function () {
                document.location.reload();
            },
            error: function (jqXHR) {
                tokenFormGroup.addClass("has-error");

                if (jqXHR.responseText == "INVALID_TOKEN") {
                    tokenInfoBox.text("Der angegebene Code ist ung\u00fcltig!");
                } else {
                    tokenInfoBox.text("Ein unbekannter Fehler ist aufgetreten (Fehler " + jqXHR.status + ")!");
                }
            }
        });
    });

    $(".settings-contact-remove").on("click", function () {
        $(this).closest(".settings-contact").remove();
    });
});