$(function () {
    $("#news-content").ckeditor({
        language: "de",
        extraPlugins: "colorbutton,image2,justify,save,uploadimage",
        uploadUrl: "internal/upload",
        baseFloatZIndex: 1000
    });

    CKEDITOR.plugins.addExternal("save", "/ckeditor_plugins/save/");

    $("#news-editor-remove-button").on("click", function () {
        $("#news-editor-remove-modal").modal("hide");

        CKEDITOR.instances["news-content"].setData("");

        $.ajax({
            url: "internal/admin/newseditor/content.html",
            method: "DELETE"
        });
    });
});