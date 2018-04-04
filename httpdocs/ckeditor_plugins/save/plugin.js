CKEDITOR.plugins.add("save", {
    init: function (editor) {
        editor.addCommand("save", {
            exec: function (editor) {
                $.ajax({
                    url: "internal/admin/newseditor/content.html",
                    method: "POST",
                    data: {
                        content: editor.getData()
                    }
                });
            }
        });

        editor.addCommand("delete", {
            exec: function () {
                $("#news-editor-remove-modal").modal("show");
            }
        });

        editor.ui.addButton("Save", {
            toolbar: "document",
            label: "Speichern",
            command: "save",
            icon: this.path + "images/save.svg"
        });

        editor.ui.addButton("Delete", {
            toolbar: "document",
            label: "L\u00f6schen",
            command: "delete",
            icon: this.path + "images/delete.svg"
        });
    }
});