$(function()
{
	var membersList = $("#members-list");

	membersList.find("th > .members-select-checkbox").on("change", function()
	{
		var checkbox = $(this);

		membersList.find("tbody > tr .members-select-checkbox").each(function()
		{
			$(this).prop("checked", checkbox.prop("checked")).change();
		});
	});

	membersList.find("tbody > tr .members-select-checkbox").on("change", function()
	{
		var rows = membersList.find("tbody > tr");
		var checkCount = 0;

		rows.each(function()
		{
			var checked = $(this).find(".members-select-checkbox").prop("checked");

			$(this).toggleClass("success", checked);

			if (checked)
			{
				checkCount++;
			}
		});

		var checkAllCheckbox = membersList.find("th > .members-select-checkbox");

		if (checkCount)
		{
			if (checkCount == rows.length)
			{
				checkAllCheckbox.prop("checked", true);
			}
		}
		else
		{
			checkAllCheckbox.prop("checked", false);
		}

		$("#members-send-message-button").toggle(!!checkCount);
	});

	membersList.find("tbody > tr").on("click", function(event)
	{
		var checkbox = $(this).find(".members-select-checkbox");

		if ($(event.target).is(checkbox))
		{
			return;
		}

		checkbox.prop("checked", !checkbox.prop("checked")).change();
	});

	$("#members-send-message-button").on("click", function()
	{
		$.get("templates/recipients.mustache", function(template)
		{
			var recipients = [];
			var recipientUserIds = [];

			membersList.find("tbody > tr").each(function()
			{
				if (!$(this).find(".members-select-checkbox").prop("checked"))
				{
					return;
				}

				recipients.push(
				{
					firstName: $(this).find("td.first-name").text(),
					lastName: $(this).find("td.last-name").text()
				});

				recipientUserIds.push($(this).data("id"));
			});

			$("#members-send-message-recipients").html(Mustache.render(template,
			{
				recipients: recipients
			}));

			$("#members-send-message-form")[0].reset();

			$("#members-send-message-recipients-list").val(JSON.stringify(recipientUserIds));

			var modal = $("#members-send-message-modal").modal("show");
		});
	});
});