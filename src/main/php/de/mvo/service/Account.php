<?php
namespace de\mvo\service;

use de\mvo\model\User;
use de\mvo\MustacheRenderer;
use de\mvo\service\exception\LoginException;
use de\mvo\utils\Image;
use PDOException;
use stdClass;

class Account extends AbstractService
{
	public function login()
	{
		if (!isset($_POST["username"]) or !isset($_POST["password"]))
		{
			http_response_code(400);
			return null;
		}

		$user = User::getByUsername($_POST["username"]);
		if ($user === null or !$user->validatePassword($_POST["password"]))
		{
			throw new LoginException(LoginException::INVALID_CREDENTIALS);
		}

		$_SESSION["userId"] = $user->id;

		if (isset($_GET["redirect"]))
		{
			header("Location: " . $_GET["redirect"], true, 302);
		}

		return null;
	}

	public function logout()
	{
		User::logout();

		return file_get_contents(VIEWS_ROOT . "/account/logout.html");
	}

	public function showSettings($message = null)
	{
		$user = User::getCurrent();

		return MustacheRenderer::render("account/settings", array
		(
			"user" => $user,
			"message" => $message
		));
	}

	private function handleProfilePictureUpload()
	{
		if (!isset($_FILES["profilePicture"]))
		{
			http_response_code(400);
			return null;
		}

		$success = false;
		$newImage = null;

		if (isset($_FILES["profilePicture"]["error"]) and $_FILES["profilePicture"]["error"])
		{
			switch ($_FILES["profilePicture"]["error"])
			{
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					return array("type" => "danger", "text" => "Maximale Dateigr&ouml;e erreicht!");
					break;
			}

			error_log("Upload error (error " . $_FILES["profilePicture"]["error"] . ")");
		}
		else
		{
			$sourceImage = imagecreatefromjpeg($_FILES["profilePicture"]["tmp_name"]);

			if (isset($_POST["profilePicture-crop"]))
			{
				$cropData = json_decode($_POST["profilePicture-crop"]);
				if ($cropData === null or !isset($cropData->w) or !isset($cropData->h) or $cropData->w <= 0 or $cropData->h <= 0)
				{
					http_response_code(400);
					return null;
				}

				if (!isset($cropData->x))
				{
					$cropData->x = 0;
				}

				if (!isset($cropData->y))
				{
					$cropData->y = 0;
				}
			}
			else
			{
				$cropData = new stdClass;

				$cropData->x = 0;
				$cropData->y = 0;
				$cropData->w = imagesx($sourceImage);
				$cropData->w = imagesy($sourceImage);
			}

			Image::calculateResize($cropData->w, $cropData->h, 600, 600, $width, $height);

			$croppedImage = imagecreatetruecolor($width, $height);
			if ($croppedImage and imagecopyresampled($croppedImage, $sourceImage, 0, 0, $cropData->x, $cropData->y, $width, $height, $cropData->w, $cropData->h))
			{
				$filename = PROFILE_PICTURES_ROOT . "/" . User::getCurrent()->id . ".jpg";
				if (imagejpeg($croppedImage, $filename))
				{
					$success = true;
				}
				else
				{
					error_log("Unable to save file to " . $filename);
				}
			}
			else
			{
				error_log("Unable to crop/resize image");
			}
		}

		if ($success)
		{
			return array("type" => "success", "text" => "Das Profilbild wurde erfolgreich hochgeladen.");
		}
		else
		{
			return array("type" => "danger", "text" => "Beim Hochladen ist ein Fehler ausgetreten. Bitte versuche es erneut oder wende dich an den Webmaster.");
		}
	}

	public function updateSettings()
	{
		if (!isset($_POST["form"]))
		{
			http_response_code(400);
			return null;
		}

		$user = User::getCurrent();

		// TODO
		switch ($_POST["form"])
		{
			case "profilepicture":
				$message = $this->handleProfilePictureUpload();
				break;
			case "account":
				if (!isset($_POST["username"]) or !isset($_POST["firstName"]) or !isset($_POST["lastName"]))
				{
					http_response_code(400);
					return null;
				}

				try
				{
					$user->setUsername($_POST["username"]);
				}
				catch (PDOException $exception)
				{
					// Duplicate username
					if ($exception->errorInfo[1] == 1062)
					{
						$message = array("type" => "danger", "text" => "Der Benutzername '" . $_POST["username"] . "' wird bereits verwendet!");
						break;
					}
				}

				$user->setName($_POST["firstName"], $_POST["lastName"]);

				$message = array("type" => "success", "text" => "Die Accountinformationen wurden erfolgreich aktualisiert.");
				break;
			case "password":
				$message = null;
				break;
			case "email":
				$message = null;
				break;
			case "contact":
				$message = null;
				break;
			default:
				http_response_code(400);
				return null;
		}

		switch ($message["type"])
		{
			case "danger":
				$message["icon"] = "exclamation-triangle";
				break;
			case "success":
				$message["icon"] = "info-circle";
		}

		return $this->showSettings($message);
	}
}