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

	$(".gallery-source a").on("click", function(event)
	{
		event.preventDefault();
		blueimp.Gallery($(".gallery-source a"),
		{
			useBootstrapModal: false,
			continuous: false,
			slideshowInterval: 10000,
			index: $(this)[0]
		});
	})
});