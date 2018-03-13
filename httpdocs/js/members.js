$(function () {
    var membersList = $("#members-list");

    membersList.find("th > .members-select-checkbox").on("change", function () {
        var checkbox = $(this);

        membersList.find("tbody > tr .members-select-checkbox").each(function () {
            $(this).prop("checked", checkbox.prop("checked")).change();
        });
    });

    membersList.find("tbody > tr .members-select-checkbox").on("change", function (event) {
        var rows = membersList.find("tbody > tr");
        var checkCount = 0;

        rows.each(function () {
            var checked = $(this).find(".members-select-checkbox").prop("checked") && !$(this).data("own-user");

            $(this).toggleClass("table-success", checked);

            if (checked) {
                checkCount++;
            }
        });

        // event.originalEvent is undefined if triggered by change() method
        if (event.originalEvent) {
            var checkAllCheckbox = membersList.find("th > .members-select-checkbox");

            if (checkCount) {
                if (checkCount == rows.length) {
                    checkAllCheckbox.prop("checked", true);
                }
            } else {
                checkAllCheckbox.prop("checked", false);
            }
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

    if (membersList.hasClass("birthdays")) {
        var nextBirthday = Members.findNextBirthday();

        if (nextBirthday) {
            var row = membersList.find("tbody > tr > td.birthdate[data-next-birthday=\"" + moment(nextBirthday).format("YYYY-MM-DD") + "\"]").parent();

            var content = row.find(".birthday-days div");
            content.removeClass("hidden");
            content.find(".badge").text(moment(nextBirthday).calendar(null, {
                sameDay: "[heute]",
                nextDay: "[morgen]",
                nextWeek: "dddd",
                sameElse: "DD.MM.YYYY"
            }));
        }
    }

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

    $("#members-send-message-attachments").on("change", "input[type='file']", function () {
        var container = $("#members-send-message-attachments");

        container.find("input[type='file']").each(function () {
            var fileList = $(this).prop("files");

            if (!fileList.length) {
                $(this).remove();
            }
        });

        var newFileInput = $("<input>");
        newFileInput.attr("type", "file");
        newFileInput.attr("name", "files[]");
        newFileInput.attr("multiple", true);

        container.append(newFileInput);
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
    },

    findNextBirthday: function () {
        var birthdays = [];

        $("#members-list").find("tbody > tr > td.birthdate").each(function () {
            birthdays.push(moment($(this).data("next-birthday")));
        });

        birthdays.sort(function (date1, date2) {
            return date1 - date2;
        });

        return birthdays[0];
    }
};