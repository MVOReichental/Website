<?php
namespace de\mvo\service;

use de\mvo\model\User;
use de\mvo\service\exception\LoginException;

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
}