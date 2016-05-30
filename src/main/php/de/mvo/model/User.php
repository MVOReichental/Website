<?php
namespace de\mvo\model;

use de\mvo\Database;
use de\mvo\model\contacts\Contacts;
use de\mvo\model\permissions\GroupList;
use PDOException;
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
	private $password;
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
	/**
	 * @var User
	 */
	private static $currentUser;

	public function __construct()
	{
		$this->id = (int) $this->id;
	}

	public static function getCurrent()
	{
		if (self::$currentUser === null and isset($_SESSION["userId"]))
		{
			self::$currentUser = self::getById($_SESSION["userId"]);
		}

		return self::$currentUser;
	}

	public static function logout()
	{
		session_unset();
		session_destroy();

		self::$currentUser = null;
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

	/**
	 * @param string $password
	 *
	 * @return bool
	 */
	public function validatePassword($password)
	{
		if (!password_verify($password, $this->password))
		{
			return false;
		}

		if (password_needs_rehash($this->password, PASSWORD_DEFAULT))
		{
			$this->setPassword($password);
		}

		return true;
	}

	public function setPassword($password)
	{
		$password = password_hash($password, PASSWORD_DEFAULT);

		$query = Database::prepare("
			UPDATE `users`
			SET `password` = :password
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":password" => $password,
			":id" => $this->id
		));

		$this->password = $password;
	}

	public function setUsername($username)
	{
		$query = Database::prepare("
			UPDATE `users`
			SET `username` = :username
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":username" => $username,
			":id" => $this->id
		));

		$this->username = $username;
	}

	public function setName($firstName, $lastName)
	{
		$query = Database::prepare("
			UPDATE `users`
			SET
				`firstName` = :firstName,
				`lastName` = :lastName
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":firstName" => $firstName,
			":lastName" => $lastName,
			":id" => $this->id
		));

		$this->firstName = $firstName;
		$this->lastName = $lastName;
	}

	public function hasPermission($permission)
	{
		return GroupList::load()->getPermissionsForUser($this)->hasPermission($permission);
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

	public function contacts()
	{
		return Contacts::forUser($this);
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