<?php
namespace de\mvo\model\users;

use ArrayObject;
use de\mvo\Database;

class Users extends ArrayObject
{
	public function hasUser(User $user)
	{
		// We can't use in_array() here because we have to compare the id property instead of the whole object.

		/**
		 * @var $thisUser User
		 */
		foreach ($this as $thisUser)
		{
			if ($thisUser->isEqualTo($user))
			{
				return true;
			}
		}

		return false;
	}

	public function addAll(Users $users)
	{
		foreach ($users as $user)
		{
			$this->append($user);
		}
	}

	public function makeUnique()
	{
		$this->exchangeArray(array_unique((array) $this));
	}

	public static function getAll()
	{
		$users = new self;

		$query = Database::query("SELECT * FROM `users`");

		while ($user = $query->fetchObject(User::class))
		{
			$users->append($user);
		}

		return $users;
	}
}