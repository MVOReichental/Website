$(function () {
    var profilePictureFileInput = $("#settings-profile-profilepicture");
    profilePictureFileInput.fileupload({
        dropZone: $(".settings-profile-profilepicture-dropzone"),
        autoUpload: false,
        maxFileSize: 999000,
        formData: function () {
            return [
                {
                    name: "crop",
                    value: JSON.stringify(profilePictureFileInput.data("crop"))
                }
            ];
        },
        add: function (event, data) {
            profilePictureFileInput.data("data", data);

            var reader = new FileReader;
            reader.onload = function (event) {
                var image = $("#settings-profile-profilepicture-crop-img");

                image.attr("src", event.target.result);
                image[0].onload = function () {
                    var oldJcrop = image.Jcrop("api");
                    if (oldJcrop) {
                        oldJcrop.destroy();
                    }

                    // TODO: Replace with Cropper.js?
                    image.Jcrop({
                        aspectRatio: 1,
                        minSize: [200, 200],
                        boxWidth: 568,
                        boxHeight: 568,
                        onSelect: function (coords) {
                            profilePictureFileInput.data("crop", {
                                x: coords.x,
                                y: coords.y,
                                width: coords.w,
                                height: coords.h
                            });
                        }
                    });

                    $("#settings-profile-profilepicture-upload-error").hide();
                    $("#settings-profile-profilepicture-crop-modal").modal("show");

                    var uploadButton = $("#settings-profile-profilepicture-upload")
                    uploadButton.find(".state-idle").show();
                    uploadButton.find(".state-loading").hide();
                    uploadButton.prop("disabled", false);
                };
            };
            reader.readAsDataURL(data.files[0]);
        },
        done: function () {
            document.location.reload();
        },
        fail: function (event, data) {
            var message = "Beim Upload ist ein Fehler aufgetreten!";

            var response = data.jqXHR.responseText;
            switch (response) {
                case "FILE_SIZE_EXCEEDED":
                    message = "Die maximale Dateigr\u00f6\u00dfe wurde erreicht!";
                    break;
                case "INVALID_FORMAT":
                    message = "Der Dateityp wird nicht unterst\u00fctzt!";
                    break;
            }

            $("#settings-profile-profilepicture-upload-error").show().find("span").text(message);
        },
        always: function () {
            var uploadButton = $("#settings-profile-profilepicture-upload")
            uploadButton.find(".state-idle").show();
            uploadButton.find(".state-loading").hide();
            uploadButton.prop("disabled", false);

            // TODO: Re-enable cropping area
        }
    });

    $("#settings-profile-profilepicture-upload").on("click", function () {
        $("#settings-profile-profilepicture-upload-error").hide();

        // TODO: Disable cropping area

        var uploadButton = $("#settings-profile-profilepicture-upload")
        uploadButton.find(".state-idle").hide();
        uploadButton.find(".state-loading").show();
        uploadButton.prop("disabled", true);

        profilePictureFileInput.data("data").submit();
    });

    $("#settings-profile-profilepicture-cancel").on("click", function () {
        profilePictureFileInput.data("data").jqXHR.abort();
    });

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
            success: function (uri) {
                $("#settings-2fa-qrcode").qrcode(uri);

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
});