$(function()
{
	var at = decodeURIComponent("%40");
	var domain = decodeURIComponent("m%75%73%69%6bv%65%72%65%69%6e%2dr%65%69%63%68%65%6eta%6c%2ede");
	var protocol = decodeURIComponent("%6dai%6cto");

	$(".contact-mail").each(function()
	{
		var address = $(this).data("mailbox") + at + domain;

		$(this).attr("href", protocol + ":" + address);
		$(this).text(address);
	});

	$(".selectpicker").each(function()
	{
		var selection = $(this).data("selection");
		if (selection !== undefined)
		{
			$(this).val(selection);
		}
	});

	$(".gallery-source a").on("click", function(event)
	{
		var allElements = $(this).parent(".gallery-source").find("a").has("img");

		event.preventDefault();
		blueimp.Gallery(allElements,
		{
			useBootstrapModal: false,
			continuous: false,
			slideshowInterval: 10000,
			index: $(this)[0]
		});
	})
});