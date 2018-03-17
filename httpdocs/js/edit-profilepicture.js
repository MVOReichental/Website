$(function () {
    var modal = $(".edit-profilepicture-crop-modal");
    var profilePictureFileInput = $("input.edit-profilepicture");
    profilePictureFileInput.fileupload({
        dropZone: $("html"),
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
                var image = modal.find("img.crop-img");

                image.attr("src", event.target.result);
                image[0].onload = function () {
                    modal.find(".upload-error").hide();
                    modal.modal("show");

                    var uploadButton = modal.find(".upload-button");
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

            modal.find(".upload-error").show().find("span").text(message);
        },
        always: function () {
            var uploadButton = modal.find(".upload-button");
            uploadButton.find(".state-idle").show();
            uploadButton.find(".state-loading").hide();
            uploadButton.prop("disabled", false);

            // TODO: Re-enable cropping area
        }
    });

    modal.find(".upload-button").on("click", function () {
        modal.find(".upload-error").hide();

        // TODO: Disable cropping area

        var uploadButton = modal.find(".upload-button");
        uploadButton.find(".state-idle").hide();
        uploadButton.find(".state-loading").show();
        uploadButton.prop("disabled", true);

        profilePictureFileInput.data("data").submit();
    });

    modal.find(".cancel-button").on("click", function () {
        var jqXHR = profilePictureFileInput.data("data").jqXHR;
        if (jqXHR) {
            jqXHR.abort();
        }
    });

    modal.on("shown.bs.modal", function () {
        modal.find("img.crop-img").cropper({
            aspectRatio: 1,
            zoomable: false,
            //minSize: [200, 200],
            crop: function (event) {
                profilePictureFileInput.data("crop", {
                    x: event.detail.x,
                    y: event.detail.y,
                    width: event.detail.width,
                    height: event.detail.height
                });
            }
        });
    });

    modal.on("hidden.bs.modal", function () {
        modal.find("img.crop-img").cropper("destroy");
    });
});