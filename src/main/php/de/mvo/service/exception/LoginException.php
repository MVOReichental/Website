<?php
namespace de\mvo\service\exception;

class LoginException extends AccountException
{
	const NOT_LOGGED_IN = "not-logged-in";
	const INVALID_CREDENTIALS = "invalid-credentials";
	const INVALID_2FA_TOKEN = "invalid-2fa-token";
	const REQUIRE_2FA_TOKEN = "require-2fa-token";

	private $type;

	public function __construct($type)
	{
		$this->type = $type;
	}

	public function getLocalizedMessage()
	{
		switch ($this->type)
		{
			case self::INVALID_CREDENTIALS:
				return "Der angegebene Benutzername oder das Passwort ist falsch!";
			case self::INVALID_2FA_TOKEN:
				return "Der angegebene Code ist ung&uuml;ltig!";
			case self::REQUIRE_2FA_TOKEN:
				return null;
			default:
				return null;
		}
	}

	public function getType()
	{
		return $this->type;
	}
}