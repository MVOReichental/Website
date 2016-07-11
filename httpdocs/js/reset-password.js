$(function()
{
	$("#reset-password-confirm-form").submit(function(event)
	{
		if ($("#reset-password-confirm-password1").val() != $("#reset-password-confirm-password2").val())
		{
			var box = $("#reset-password-confirm-box");

			box.addClass("has-error");
			box.find(".help-block").text("Die Passw\u00f6rter stimmen nicht \u00fcberein!");

			event.preventDefault();
		}
	});
});