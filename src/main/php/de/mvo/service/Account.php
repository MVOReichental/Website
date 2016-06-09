<?php
namespace de\mvo\service;

use de\mvo\model\users\User;
use de\mvo\MustacheRenderer;
use de\mvo\service\exception\LoginException;
use Exception;
use Kelunik\TwoFactor\Oath;
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
		if (isset($_POST["username"]) and isset($_POST["password"]))
		{
			$user = User::getByUsername($_POST["username"]);
			if ($user === null or !$user->validatePassword($_POST["password"]))
			{
				throw new LoginException(LoginException::INVALID_CREDENTIALS);
			}

			if ($user->has2FA())
			{
				$_SESSION["2faLoginUserId"] = $user->id;

				throw new LoginException(LoginException::REQUIRE_2FA_TOKEN);
			}

			$_SESSION["userId"] = $user->id;
		}
		elseif (isset($_SESSION["2faLoginUserId"]) and isset($_POST["2fa-token"]))
		{
			$user = User::getById($_SESSION["2faLoginUserId"]);
			if ($user === null)
			{
				throw new Exception("User not found");
			}

			if (!$user->validateTotp($_POST["2fa-token"]))
			{
				throw new LoginException(LoginException::INVALID_2FA_TOKEN);
			}

			unset($_SESSION["2faLoginUserId"]);

			$_SESSION["userId"] = $user->id;
		}
		else
		{
			throw new LoginException(LoginException::UNKNOWN_ERROR);
		}

		if (isset($_GET["redirect"]) and $_GET["redirect"] != "")
		{
			header("Location: " . $_GET["redirect"], true, 302);
		}
		else
		{
			header("Location: /intern", true, 302);
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
					else
					{
						throw $exception;
					}
				}

				$message = array("type" => "success", "text" => "Der Benutzername wurde erfolgreich ge&auml;ndert!");
				break;
			case "profile":
				if (!isset($_POST["firstName"]) or !isset($_POST["lastName"]))
				{
					http_response_code(400);
					return null;
				}

				$user->setName($_POST["firstName"], $_POST["lastName"]);

				$message = array("type" => "success", "text" => "Dein Benutzerprofil wurde erfolgreich aktualisiert.");
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

				$newPassword = $_POST["newPassword"];
				if (strlen($newPassword) < 6)
				{
					$message = array("type" => "danger", "text" => "Das Passwort muss aus mindestens 6 Zeichen bestehen!");
					break;
				}

				$user->setPassword($newPassword);

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

	public function request2faKey()
	{
		if (!isset($_POST["password"]))
		{
			http_response_code(400);
			return null;
		}

		$user = User::getCurrent();

		if (!$user->validatePassword($_POST["password"]))
		{
			http_response_code(401);
			echo "INVALID_PASSWORD";
			return null;
		}

		$oath = new Oath;

		$key = $oath->generateKey();

		$uri = $oath->getUri($key, "MVO", $user->username);

		$_SESSION["2faKey"] = $key;

		header("Content-Type: text/plain");
		echo $uri;

		return null;
	}

	public function enable2fa()
	{
		if (!isset($_SESSION["2faKey"]) or !isset($_POST["token"]))
		{
			http_response_code(400);
			return null;
		}

		$user = User::getCurrent();

		if (!$user->validateTotp($_POST["token"], $_SESSION["2faKey"]))
		{
			http_response_code(400);
			echo "INVALID_TOKEN";
			return null;
		}

		$user->setTotpKey($_SESSION["2faKey"]);

		return null;
	}

	public function disable2fa()
	{
		if (!isset($_POST["password"]))
		{
			http_response_code(400);
			return null;
		}

		$user = User::getCurrent();

		if (!$user->validatePassword($_POST["password"]))
		{
			http_response_code(401);
			echo "INVALID_PASSWORD";
			return null;
		}

		$user->setTotpKey(null);

		return null;
	}
}