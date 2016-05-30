$(function()
{
	$("#settings-account-profilepicture").on("change", function()
	{
		if (this.files == undefined || !this.files.length)
		{
			return;
		}

		var reader = new FileReader;
		reader.onload = function(event)
		{
			var image = $("#settings-account-profilepicture-crop-img");

			image.attr("src", event.target.result);
			image[0].onload = function()
			{
				var oldJcrop = image.data("jcrop");
				if (oldJcrop)
				{
					oldJcrop.destroy();
				}

				image.Jcrop(
				{
					aspectRatio: 1,
					minSize: [200, 200],
					boxWidth: 568,
					boxHeight: 568,
					onSelect: function(coords)
					{
						$("#settings-account-profilepicture-crop-coords").val(JSON.stringify(coords));
					}
				}, function()
				{
					image.data("jcrop", this);
				});

				$("#settings-account-profilepicture-crop-modal").modal("show");
			};
		};
		reader.readAsDataURL(this.files[0]);
	});

	$("#settings-account-profilepicture-upload").on("click", function()
	{
		$("#settings-account-profilepicture-form").submit();
	});
});