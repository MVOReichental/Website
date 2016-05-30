<?php
namespace de\mvo\model;

use ArrayObject;
use de\mvo\Database;

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