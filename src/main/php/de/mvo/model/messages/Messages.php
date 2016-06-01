<?php
namespace de\mvo\model\messages;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\User;
use PDO;

class Messages extends ArrayObject
{
	public static function getAll($limit = 1000)
	{
		$query = Database::prepare("
			SELECT *
			FROM `messages`
			ORDER BY `id` DESC
			LIMIT :limit
		");

		$query->bindValue(":limit", $limit, PDO::PARAM_INT);

		$query->execute();

		$messages = new self;

		while ($message = $query->fetchObject(Message::class))
		{
			$messages->append($message);
		}

		return $messages;
	}

	public static function getBySender(User $user, $limit = 1000)
	{
		$query = Database::prepare("
			SELECT *
			FROM `messages`
			WHERE `senderUserId` = :senderUserId
			ORDER BY `id` DESC
			LIMIT :limit
		");

		$query->bindValue(":senderUserId", $user->id, PDO::PARAM_INT);
		$query->bindValue(":limit", $limit, PDO::PARAM_INT);

		$query->execute();

		$messages = new self;

		while ($message = $query->fetchObject(Message::class))
		{
			$messages->append($message);
		}

		return $messages;
	}

	public static function getByRecipientAndSender(User $recipient, User $sender, $limit = 1000)
	{
		$query = Database::prepare("
			SELECT *
			FROM `messagerecipients`
			LEFT JOIN `messages` ON `messages`.`id` = `messagerecipients`.`messageId`
			WHERE `messagerecipients`.`userId` = :recipientUserId AND `messages`.`senderUserId` = :senderUserId
			ORDER BY `messages`.`id` DESC
			LIMIT :limit
		");

		$query->bindValue(":recipientUserId", $recipient->id, PDO::PARAM_INT);
		$query->bindValue(":senderUserId", $sender->id, PDO::PARAM_INT);
		$query->bindValue(":limit", $limit, PDO::PARAM_INT);

		$query->execute();

		$messages = new self;

		while ($message = $query->fetchObject(Message::class))
		{
			$messages->append($message);
		}

		return $messages;
	}
}