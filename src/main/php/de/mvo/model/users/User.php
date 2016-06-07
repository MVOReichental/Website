<?php
namespace de\mvo\model\users;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\contacts\Contacts;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\permissions\Permissions;
use Kelunik\TwoFactor\Oath;
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
	 * @var Date
	 */
	public $birthDate;
	/**
	 * @var string
	 */
	private $totpKey;
	/**
	 * @var Permissions|null|false The permissions of the user, null if not loaded yet, false if the user does not have any permission
	 */
	private $permissions;
	/**
	 * @var User
	 */
	private static $currentUser;

	public function __construct()
	{
		$this->id = (int) $this->id;

		if ($this->birthDate !== null)
		{
			$this->birthDate = new Date($this->birthDate);
		}
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
		if ($this->permissions === null)
		{
			$this->permissions = GroupList::load()->getPermissionsForUser($this);
			if ($this->permissions === null)
			{
				$this->permissions = false;
			}
		}

		if ($this->permissions === false)
		{
			return false;
		}

		if ($this->permissions->hasPermission("*"))
		{
			return true;
		}

		return $this->permissions->hasPermission($permission, false);
	}

	public function has2FA()
	{
		return $this->totpKey !== null;
	}

	public function setTotpKey($key)
	{
		$query = Database::prepare("
			UPDATE `users`
			SET `totpKey` = :totpKey
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":totpKey" => $key,
			":id" => $this->id
		));
	}

	public function validateTotp($token, $key = null)
	{
		if ($key === null)
		{
			$key = $this->totpKey;
		}

		$oath = new Oath;

		if (!$oath->verifyTotp($key, $token))
		{
			return false;
		}

		// Cleanup token lock table
		Database::query("
			DELETE FROM `usedtotptokens`
			WHERE `date` < DATE_SUB(NOW(), INTERVAL 90 SECOND)
		");

		$query = Database::prepare("
			SELECT `id` FROM `usedtotptokens`
			WHERE `userId` = :userId AND `token` = :token
		");

		$query->execute(array
		(
			":userId" => $this->id,
			":token" => $token
		));

		if ($query->rowCount())
		{
			return false;
		}

		$query = Database::prepare("
			INSERT INTO `usedtotptokens`
			SET
				`userId` = :userId,
				`token` = :token,
				`date` = NOW()
		");

		try
		{
			$query->execute(array
			(
				":userId" => $this->id,
				":token" => $token
			));

			return true;
		}
		catch (PDOException $exception)
		{
			// Duplicate token
			if ($exception->errorInfo[1] == 1062)
			{
				return false;
			}
			else
			{
				throw $exception;
			}
		}
	}

	public function isEqualTo(User $user)
	{
		return ($this->id == $user->id);
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