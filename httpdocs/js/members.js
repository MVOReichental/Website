$(function () {
    var membersList = $("#members-list");

    membersList.find("th > .members-select-checkbox").on("change", function () {
        var checkbox = $(this);

        membersList.find("tbody > tr .members-select-checkbox").each(function () {
            $(this).prop("checked", checkbox.prop("checked")).change();
        });
    });

    membersList.find("tbody > tr .members-select-checkbox").on("change", function () {
        var rows = membersList.find("tbody > tr");
        var checkCount = 0;

        rows.each(function () {
            var checked = $(this).find(".members-select-checkbox").prop("checked") && !$(this).data("own-user");

            $(this).toggleClass("success", checked);

            if (checked) {
                checkCount++;
            }
        });

        var checkAllCheckbox = membersList.find("th > .members-select-checkbox");

        if (checkCount) {
            if (checkCount == rows.length) {
                checkAllCheckbox.prop("checked", true);
            }
        } else {
            checkAllCheckbox.prop("checked", false);
        }

        $("#members-send-message-button").toggle(!!checkCount);
    });

    membersList.find("tbody > tr").on("click", function (event) {
        var checkbox = $(this).find(".members-select-checkbox");

        if ($(event.target).is(checkbox)) {
            return;
        }

        checkbox.prop("checked", !checkbox.prop("checked")).change();
    });

    $("#members-send-message-button").on("click", function () {
        var recipients = [];

        membersList.find("tbody > tr").each(function () {
            if (!$(this).find(".members-select-checkbox").prop("checked") || $(this).data("own-user")) {
                return;
            }

            recipients.push({
                id: $(this).data("id"),
                firstName: $(this).find("td.first-name").text(),
                lastName: $(this).find("td.last-name").text()
            });
        });

        Members.showSendMessage(recipients);
    });

    $("#members-details-send-message-button").on("click", function () {
        var recipients = [
            {
                id: $("#members-details-user").data("id"),
                firstName: $("#members-details-firstname").text(),
                lastName: $("#members-details-lastname").text()
            }
        ];

        Members.showSendMessage(recipients);
    });
});

var Members = {
    showSendMessage: function (recipients) {
        $.get("templates/recipients.mustache", function (template) {
            var recipientUserIds = [];

            for (var index = 0; index < recipients.length; index++) {
                recipientUserIds.push(recipients[index].id);
            }

            $("#members-send-message-recipients").html(Mustache.render(template, {
                recipients: recipients
            }));

            $("#members-send-message-form")[0].reset();

            $("#members-send-message-recipients-list").val(JSON.stringify(recipientUserIds));

            var modal = $("#members-send-message-modal").modal("show");
        });
    }
};