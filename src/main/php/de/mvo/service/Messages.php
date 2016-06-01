<?php
namespace de\mvo\service;

use de\mvo\model\messages\Messages as MessagesList;
use de\mvo\model\users\User;
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
}