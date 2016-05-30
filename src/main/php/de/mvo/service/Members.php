<?php
namespace de\mvo\service;

use de\mvo\model\MembersList;
use de\mvo\MustacheRenderer;

class Members extends AbstractService
{
	public function getList()
	{
		return MustacheRenderer::render("members", array
		(
			"members" => new MembersList
		));
	}
}