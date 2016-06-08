<?php
namespace de\mvo\service;

use ArrayObject;
use de\mvo\model\messages\Message;
use de\mvo\model\messages\Messages as MessagesList;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\MustacheRenderer;

class Messages extends AbstractService
{
	public function getSentMessages()
	{
		return MustacheRenderer::render("messages/page", array
		(
			"title" => "Gesendete Nachrichten",
			"messages" => MessagesList::getBySender(User::getCurrent())
		));
	}

	public function getReceivedMessages()
	{
		return MustacheRenderer::render("messages/page", array
		(
			"title" => "Empfangene Nachrichten",
			"messages" => MessagesList::getByRecipient(User::getCurrent())
		));
	}

	public function sendMessage()
	{
		if (!isset($_POST["text"]) or !isset($_POST["recipients"]))
		{
			http_response_code(400);
			return null;
		}

		$recipients = json_decode($_POST["recipients"]);
		if ($recipients === null or !is_array($recipients))
		{
			http_response_code(400);
			return null;
		}

		$message = new Message(true);

		$message->sender = User::getCurrent();
		$message->text = $_POST["text"];

		$message->recipients = new Users;

		foreach ($recipients as $userId)
		{
			$user = User::getById($userId);
			if ($user === null)
			{
				continue;// TODO: Cancel sending message?
			}

			$message->recipients->append($user);
		}

		$message->saveAsNew();

		$message = Message::getById($message->id);

		return MustacheRenderer::render("messages/send-success", array
		(
			"content" => MustacheRenderer::render("messages/list", array
			(
				"messages" => new ArrayObject(array($message))
			))
		));
	}
}