<?php
namespace de\mvo\service;

use de\mvo\model\GroupMembersList;
use de\mvo\TwigRenderer;
use Twig\Error\Error;

class GroupMembers extends AbstractService
{
    /**
     * @param $title
     * @param $group
     * @return string
     * @throws Error
     */
    public function get($title, $group)
    {
        return TwigRenderer::render("groupmembers", array
        (
            "title" => $title,
            "groups" => new GroupMembersList($group)
        ));
    }
}