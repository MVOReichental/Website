<?php
namespace de\mvo\service;

use de\mvo\model\User;

class ProfilePicture extends AbstractService
{
	public function get()
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

		header("Content-Type: image/jpeg");
		readfile($filename);
		return null;
	}
}