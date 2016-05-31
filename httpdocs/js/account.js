$(function()
{
	var profilePictureFileInput = $("#settings-profile-profilepicture");
	profilePictureFileInput.fileupload(
	{
		dropZone: $(".settings-profile-profilepicture-dropzone"),
		autoUpload: false,
		maxFileSize: 999000,
		formData: function()
		{
			return [
				{
					name: "crop",
					value: JSON.stringify(profilePictureFileInput.data("crop"))
				}
			];
		},
		add: function(event, data)
		{
			profilePictureFileInput.data("data", data);

			var reader = new FileReader;
			reader.onload = function(event)
			{
				var image = $("#settings-profile-profilepicture-crop-img");

				image.attr("src", event.target.result);
				image[0].onload = function()
				{
					var oldJcrop = image.Jcrop("api");
					if (oldJcrop)
					{
						oldJcrop.destroy();
					}

					// TODO: Replace with Cropper.js?
					image.Jcrop(
					{
						aspectRatio: 1,
						minSize: [200, 200],
						boxWidth: 568,
						boxHeight: 568,
						onSelect: function(coords)
						{
							profilePictureFileInput.data("crop",
							{
								x: coords.x,
								y: coords.y,
								width: coords.w,
								height: coords.h
							});
						}
					});

					$("#settings-profile-profilepicture-upload-error").hide();
					$("#settings-profile-profilepicture-crop-modal").modal("show");

					var uploadButton = $("#settings-profile-profilepicture-upload")
					uploadButton.find(".state-idle").show();
					uploadButton.find(".state-loading").hide();
					uploadButton.prop("disabled", false);
				};
			};
			reader.readAsDataURL(data.files[0]);
		},
		done: function()
		{
			document.location.reload();
		},
		fail: function(event, data)
		{
			var message = "Beim Upload ist ein Fehler aufgetreten!";

			var response = data.jqXHR.responseText;
			switch (response)
			{
				case "FILE_SIZE_EXCEEDED":
					message = "Die maximale Dateigr\u00f6\u00dfe wurde erreicht!";
					break;
			}

			$("#settings-profile-profilepicture-upload-error").show().find("span").text(message);
		},
		always: function()
		{
			var uploadButton = $("#settings-profile-profilepicture-upload")
			uploadButton.find(".state-idle").show();
			uploadButton.find(".state-loading").hide();
			uploadButton.prop("disabled", false);

			// TODO: Re-enable cropping area
		}
	});

	$("#settings-profile-profilepicture-upload").on("click", function()
	{
		$("#settings-profile-profilepicture-upload-error").hide();

		// TODO: Disable cropping area

		var uploadButton = $("#settings-profile-profilepicture-upload")
		uploadButton.find(".state-idle").hide();
		uploadButton.find(".state-loading").show();
		uploadButton.prop("disabled", true);

		profilePictureFileInput.data("data").submit();
	});

	$("#settings-profile-profilepicture-cancel").on("click", function()
	{
		profilePictureFileInput.data("data").jqXHR.abort();
	});
});