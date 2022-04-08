import $ from "jquery";
import "jstree-bootstrap-theme/dist/jstree";
import Permissions2jsTree from "./permissions";

$(function () {
    var form = $("#usermanagement-edit-form");

    form.on("submit", function (event) {
        var element = $("#usermanagement-edit-username");
        if (!element.val()) {
            var divElement = element.parent(".form-group");

            divElement.addClass("has-error");// TODO
            divElement.find(".form-text").text("Ein Benutzername ist erforderlich!");

            event.preventDefault();
        }

        var nodes = $("#usermanagement-edit-tab-permissiongroups").jstree(true).get_checked(true);

        var groupIds = [];

        $(nodes).each(function (index, node) {
            groupIds.push(node.id);
        });

        $(nodes).each(function (index, node) {
            if (groupIds.indexOf(node.parent) !== -1) {
                var foundIndex = groupIds.indexOf(node.id);
                if (foundIndex !== -1) {
                    groupIds.splice(foundIndex, 1);
                }
            }
        });

        $("#usermanagement-edit-permission-groups").val(groupIds.join(","));
    });

    form.find(".nav-tabs a").on("click", function (event) {
        event.preventDefault();
        $(this).tab("show");
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

    $.getJSON("internal/admin/usermanagement/permission-groups", function (data) {
        var treeElement = $("#usermanagement-edit-tab-permissiongroups");

        treeElement.jstree({
            core: {
                themes: {
                    name: "proton",
                    responsive: true
                },
                data: Permissions2jsTree.convertGroupList(data, $("#usermanagement-edit-permission-groups").val().split(","))
            },
            plugins: ["checkbox"]
        });
    });
});