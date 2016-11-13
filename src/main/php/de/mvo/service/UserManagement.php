<?php
namespace de\mvo\service;

use de\mvo\model\users\Users;
use de\mvo\TwigRenderer;

class UserManagement extends AbstractService
{
    public function getPage()
    {
        return TwigRenderer::render("admin/usermanagement/page", array
        (
            "users" => Users::getAll()
        ));
    }
}