<?php
namespace de\mvo\service;

use de\mvo\model\messages\Messages;
use de\mvo\model\users\User;
use de\mvo\MustacheRenderer;

class InternHome extends AbstractService
{
	public function get()
	{
		$currentUser = User::getCurrent();

		if ($currentUser->hasPermission("messages.readAll"))
		{
			$latestMessage = Messages::getAll(1);
		}
		else
		{
			$receivedMessages = Messages::getByRecipient($currentUser, 1);
			$sentMessages = Messages::getBySender($currentUser, 1);

			if (!$receivedMessages->count())
			{
				$latestMessage = $sentMessages;
			}
			elseif (!$sentMessages->count())
			{
				$latestMessage = $receivedMessages;
			}
			elseif ($sentMessages->offsetGet(0)->id > $receivedMessages->offsetGet(0)->id)
			{
				$latestMessage = $sentMessages;
			}
			else
			{
				$latestMessage = $receivedMessages;
			}
		}

		return MustacheRenderer::render("home-intern", array
		(
			"user" => User::getCurrent(),
			"messages" => $latestMessage
		));
	}
}