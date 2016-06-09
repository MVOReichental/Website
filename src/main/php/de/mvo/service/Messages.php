<?php
namespace de\mvo\service;

use ArrayObject;
use de\mvo\model\messages\Message;
use de\mvo\model\messages\Messages as MessagesList;
use de\mvo\model\uploads\Upload;
use de\mvo\model\uploads\Uploads;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\MustacheRenderer;
use de\mvo\UploadHandler;
use de\mvo\uploadhandler\File;
use de\mvo\uploadhandler\Files;

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

		$files = new Files($_FILES["files"]);

		/**
		 * @var $file File
		 */
		foreach ($files as $file)
		{
			switch ($file->error)
			{
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_FORM_SIZE:
				case UPLOAD_ERR_INI_SIZE:
					return MustacheRenderer::render("messages/send-error", array
					(
						"message" => "Die maximale Dateigr&ouml;&szlig;e wurde erreicht!"
					));
					break;
				default:
					return MustacheRenderer::render("messages/send-error", array
					(
						"message" => "Beim Hochladen ist ein Fehler aufgetreten!"
					));
			}
		}

		$message = new Message(true);

		$message->sender = User::getCurrent();
		$message->text = $_POST["text"];

		$message->attachments = new Uploads;

		/**
		 * @var $file File
		 */
		foreach ($files as $file)
		{
			$upload = Upload::add($file->tempName, $file->name);
			if ($upload === null)
			{
				return MustacheRenderer::render("messages/send-error", array
				(
					"message" => "Beim Hochladen ist ein Fehler aufgetreten!"
				));
			}

			$message->attachments->append($upload);
		}

		$message->recipients = new Users;

		foreach ($recipients as $userId)
		{
			$user = User::getById($userId);
			if ($user === null)
			{
				continue;
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