<?php
namespace de\mvo\service;

use de\mvo\model\MembersList;
use de\mvo\model\messages\Message;
use de\mvo\model\messages\Messages;
use de\mvo\model\users\User;
use de\mvo\MustacheRenderer;

class Members extends AbstractService
{
	public function getList()
	{
		return MustacheRenderer::render("members/list", array
		(
			"members" => new MembersList
		));
	}

	public function getDetails()
	{
		$currentUser = User::getCurrent();
		$user = User::getByUsername($this->params->username);

		$messages = Messages::getBySender($user);

		$filteredMessages = new Messages;

		/**
		 * @var $message Message
		 */
		foreach ($messages as $message)
		{
			if ($message->sender->isEqualTo($currentUser) or $message->recipients->hasUser($currentUser))
			{
				$filteredMessages->append($message);
			}
		}

		return MustacheRenderer::render("members/details", array
		(
			"user" => $user,
			"messages" => $filteredMessages
		));
	}
}