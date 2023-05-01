CKEDITOR.plugins.add("save", {
    init: function (editor) {
        var saveUrl = editor.element.data("save-url");
        if (saveUrl) {
            editor.addCommand("save", {
                exec: function (editor) {
                    $.ajax({
                        url: saveUrl,
                        method: "POST",
                        data: {
                            content: editor.getData()
                        },
                        error: function () {
                            $.notify({
                                icon: "fas fa-exclamation-triangle",
                                message: "Beim Speichern ist ein Fehler aufgetreten!"
                            }, {
                                type: "danger"
                            });
                        },
                        success: function () {
                            $.notify({
                                message: "Der Inhalt wurde erfolgreich gespeichert."
                            }, {
                                type: "success"
                            });
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
    }
});