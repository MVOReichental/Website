<?php
namespace de\mvo\model;

use de\mvo\Database;
use RuntimeException;

class User
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $username;
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var string
	 */
	public $firstName;
	/**
	 * @var string
	 */
	public $lastName;

	public function __construct()
	{
		$this->id = (int) $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return User|null
	 */
	public static function getById($id)
	{
		$query = Database::prepare("
			SELECT *
			FROM `users`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $id
		));

		if (!$query->rowCount())
		{
			return null;
		}

		return $query->fetchObject(self::class);
	}

	/**
	 * @param string $username
	 *
	 * @return User|null
	 */
	public static function getByUsername($username)
	{
		$query = Database::prepare("
			SELECT *
			FROM `users`
			WHERE `username` = :username
		");

		$query->execute(array
		(
			":username" => $username
		));

		if (!$query->rowCount())
		{
			return null;
		}

		return $query->fetchObject(self::class);
	}

	public static function getProfilePicturePath($userId)
	{
		$filename = PROFILE_PICTURES_ROOT . "/" . $userId . ".jpg";
		if (!file_exists($filename))
		{
			$filename = PROFILE_PICTURES_ROOT . "/default.jpg";
		}

		return $filename;
	}

	public function profilePictureHash()
	{
		return md5_file(self::getProfilePicturePath($this->id));
	}

	public function __sleep()
	{
		return array("id");
	}

	public function __wakeup()
	{
		$user = self::getById($this->id);
		if ($user === null)
		{
			throw new RuntimeException("Unable to load user '" . $this->id . "'");
		}

		foreach (get_object_vars($user) as $name => $value)
		{
			$this->{$name} = $value;
		}
	}
}