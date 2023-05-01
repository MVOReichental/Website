import $ from "jquery";
import "ckeditor4";
import "ckeditor4/adapters/jquery";
import "bootstrap-notify";


$(function () {
    $("#news-editor-content").on("load", function () {
        $("#news-editor-content").ckeditor({
            language: "de",
            extraPlugins: "colorbutton,image2,justify,save,uploadimage",
            uploadUrl: "internal/upload",
            height: 500
        });

        CKEDITOR.plugins.addExternal("save", "/ckeditor_plugins/save/");

        $("#news-editor-remove-button").on("click", function () {
            $("#news-editor-remove-modal").modal("hide");

            CKEDITOR.instances["news-editor-content"].setData("");

            $.ajax({
                url: $("#news-editor-content").data("save-url"),
                method: "DELETE",
                error: function () {
                    $.notify({
                        icon: "fas fa-exclamation-triangle",
                        message: "Beim Entfernen ist ein Fehler aufgetreten!"
                    }, {
                        type: "danger"
                    });
                },
                success: function () {
                    $.notify({
                        message: "Der Inhalt wurde erfolgreich entfernt."
                    }, {
                        type: "success"
                    });
                }
            });
        });
    });
});