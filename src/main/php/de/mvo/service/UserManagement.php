<?php
namespace de\mvo\service;

use de\mvo\Date;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\service\exception\NotFoundException;
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

    public function getEditPage()
    {
        if (isset($this->params->id)) {
            $user = User::getById($this->params->id);
        } else {
            $user = null;
        }

        return TwigRenderer::render("admin/usermanagement/edit", array
        (
            "user" => $user
        ));
    }

    public function createUser()
    {
        $user = new User;

        self::setUserDataFromPostData($user);

        $user->save();

        header("Location: /internal/admin/usermanagement");
    }

    public function updateUser()
    {
        $user = User::getById($this->params->id);

        if ($user === null) {
            throw new NotFoundException;
        }

        self::setUserDataFromPostData($user);

        $user->save();

        header("Location: /internal/admin/usermanagement");
    }

    private static function setUserDataFromPostData(User $user)
    {
        $user->username = $_POST["username"];
        $user->firstName = $_POST["firstName"];
        $user->lastName = $_POST["lastName"];
        $user->email = $_POST["email"];
        $user->enabled = (bool)$_POST["enabled"];

        $birthDate = $_POST["birthDate"];
        if ($birthDate !== null and $birthDate !== "") {
            $user->birthDate = new Date($birthDate);
        }
    }
}