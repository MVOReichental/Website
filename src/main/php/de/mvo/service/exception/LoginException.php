<?php
namespace de\mvo\service\exception;

class LoginException extends AccountException
{
	const NOT_LOGGED_IN = "not-logged-in";
	const INVALID_CREDENTIALS = "invalid-credentials";

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
			default:
				return null;
		}
	}
}