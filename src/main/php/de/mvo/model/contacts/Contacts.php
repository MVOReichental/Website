<?php
namespace de\mvo\model\contacts;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\User;

class Contacts extends ArrayObject
{
	public static function forUser(User $user)
	{
		$contacts = new self;

		$query = Database::prepare("
			SELECT *
			FROM `usercontacts`
			WHERE `userId` = :userId
		");

		$query->execute(array
		(
			":userId" => $user->id
		));

		while ($contact = $query->fetchObject(Contact::class))
		{
			$contacts->append($contact);
		}

		return $contacts;
	}
}