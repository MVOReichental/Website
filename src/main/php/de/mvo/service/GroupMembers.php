<?php
namespace de\mvo\service;

use de\mvo\model\GroupMembersList;
use de\mvo\MustacheRenderer;

class GroupMembers extends AbstractService
{
	public function get($title, $group)
	{
		return MustacheRenderer::render("groupmembers", array
		(
			"title" => $title,
			"groups" => new GroupMembersList($group)
		));
	}
}