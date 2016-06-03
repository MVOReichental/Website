<?php
namespace de\mvo\model\messages;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use Parsedown;

class Message
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var Date
	 */
	public $date;
	/**
	 * @var User
	 */
	public $sender;
	/**
	 * @var Users
	 */
	public $recipients;
	/**
	 * @var string
	 */
	public $text;

	private $senderUserId;

	public function __construct()
	{
		$this->id = (int) $this->id;
		$this->date = new Date($this->date);
		$this->sender = User::getById($this->senderUserId);

		$query = Database::prepare("
			SELECT `users`.*
			FROM `messagerecipients`
			LEFT JOIN `users` ON `users`.`id` = `messagerecipients`.`userId`
			WHERE `messageId` = :messageId
		");

		$query->execute(array
		(
			":messageId" => $this->id
		));

		$this->recipients = new Users;

		while ($user = $query->fetchObject(User::class))
		{
			$this->recipients->append($user);
		}
	}

	public function formatText()
	{
		return Parsedown::instance()->text($this->text);
	}
}