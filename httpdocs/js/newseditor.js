$(function () {
    $("#news-content").ckeditor({
        language: "de",
        extraPlugins: "colorbutton,dragresize,inlinesave,justify,uploadimage",
        uploadUrl: "/internal/upload",
        inlinesave: {
            postUrl: "/internal/admin/newseditor"
        }
    });

    CKEDITOR.plugins.addExternal("inlinesave", "/ckeditor_plugins/inlinesave/");
    CKEDITOR.plugins.addExternal("dragresize", "/ckeditor_plugins/dragresize/");
});