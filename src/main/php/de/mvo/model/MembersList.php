<?php
namespace de\mvo\model;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\User;

class MembersList extends ArrayObject
{
	public function __construct()
	{
		$query = Database::query("SELECT * FROM `users`");

		while ($user = $query->fetchObject(User::class))
		{
			$this->append($user);
		}
	}
}