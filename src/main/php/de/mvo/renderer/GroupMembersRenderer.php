<?php
namespace de\mvo\renderer;

use de\mvo\model\GroupMembersList;
use de\mvo\renderer\utils\MustacheRenderer;

class GroupMembersRenderer extends AbstractRenderer
{
	private $title;
	private $group;

	public function __construct($title, $group)
	{
		$this->title = $title;
		$this->group = $group;
	}

	public function render()
	{
		return MustacheRenderer::render("groupmembers", array
		(
			"title" => $this->title,
			"groups" => new GroupMembersList($this->group)
		));
	}
}