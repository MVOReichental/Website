<?php
namespace de\mvo\service;

use de\mvo\model\protocols\ProtocolsList;
use de\mvo\model\users\User;
use de\mvo\TwigRenderer;

class Protocols extends AbstractService
{
	public function getList()
	{
		return TwigRenderer::render("protocols/page", array
		(
			"protocols" => ProtocolsList::get()->getVisibleForUser(User::getCurrent())
		));
	}
}