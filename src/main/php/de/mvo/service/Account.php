<?php
namespace de\mvo\service;

use de\mvo\model\User;
use de\mvo\MustacheRenderer;
use de\mvo\service\exception\LoginException;
use PDOException;

class Account extends AbstractService
{
	public static function getSettingsPages()
	{
		return array
		(
			"profile" => array
			(
				"title" => "Profil"
			),
			"account" => array
			(
				"title" => "Account"
			),
			"email" => array
			(
				"title" => "Email-Adresse"
			),
			"contact" => array
			(
				"title" => "Kontakt"
			)
		);
	}

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

		$pages = self::getSettingsPages();

		$activePage = null;

		foreach ($pages as $name => &$page)
		{
			$page["name"] = $name;

			if ($this->params->page == $page["name"])
			{
				$page["active"] = true;

				$activePage = $page;
			}
			else
			{
				$page["active"] = false;
			}
		}

		return MustacheRenderer::render("account/settings/page", array
		(
			"message" => $message,
			"pages" => array_values($pages),
			"title" => $activePage["title"],
			"content" => MustacheRenderer::render("account/settings/" . $activePage["name"], array
			(
				"user" => $user
			))
		));
	}

	public function updateSettings()
	{
		if (!isset($_POST["form"]))
		{
			http_response_code(400);
			return null;
		}

		$user = User::getCurrent();

		switch ($_POST["form"])
		{
			case "username":
				if (!isset($_POST["username"]))
				{
					http_response_code(400);
					return null;
				}

				$username = trim($_POST["username"]);

				if (strlen($username) < 3)
				{
					$message = array("type" => "danger", "text" => "Der Benutzername muss aus mindestens 3 Zeichen bestehen!");
					break;
				}

				try
				{
					$user->setUsername($username);
				}
				catch (PDOException $exception)
				{
					// Duplicate username
					if ($exception->errorInfo[1] == 1062)
					{
						$message = array("type" => "danger", "text" => "Der Benutzername '" . $username . "' wird bereits verwendet!");
						break;
					}
				}

				$message = array("type" => "success", "text" => "Der Benutzername wurde erfolgreich ge&auml;ndert!");
				break;
			case "account":
				if (!isset($_POST["firstName"]) or !isset($_POST["lastName"]))
				{
					http_response_code(400);
					return null;
				}

				$user->setName($_POST["firstName"], $_POST["lastName"]);

				$message = array("type" => "success", "text" => "Die Accountinformationen wurden erfolgreich aktualisiert.");
				break;
			case "password":
				if (!isset($_POST["currentPassword"]) or !isset($_POST["newPassword"]))
				{
					http_response_code(400);
					return null;
				}

				if (!$user->validatePassword($_POST["currentPassword"]))
				{
					$message = array("type" => "danger", "text" => "Das angegebene Passwort ist nicht g&uuml;tig!");
					break;
				}

				$user->setPassword($_POST["newPassword"]);

				$message = array("type" => "success", "text" => "Das Passwort wurde erfolgreich ge&auml;ndert!");
				break;
			case "email":
				$message = null;// TODO
				break;
			case "contact":
				$message = null;// TODO
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