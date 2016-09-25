<?php
namespace de\mvo\service;

use de\mvo\model\GroupMembersList;
use de\mvo\TwigRenderer;

class GroupMembers extends AbstractService
{
    public function get($title, $group)
    {
        return TwigRenderer::render("groupmembers", array
        (
            "title" => $title,
            "groups" => new GroupMembersList($group)
        ));
    }
}