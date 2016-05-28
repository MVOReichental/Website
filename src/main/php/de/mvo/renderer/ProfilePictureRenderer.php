<?php
namespace de\mvo\renderer;

use de\mvo\model\User;

class ProfilePictureRenderer extends AbstractRenderer
{
	public function render()
	{
		if (!isset($_GET["hash"]))
		{
			http_response_code(400);
			echo "Missing 'hash' parameter";
			return null;
		}

		$filename = User::getProfilePicturePath($this->params->id);

		if (md5_file($filename) != $_GET["hash"])
		{
			http_response_code(404);
			echo "File not found";
			return null;
		}

		readfile($filename);
		return null;
	}
}