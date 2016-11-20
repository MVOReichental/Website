<?php
namespace de\mvo\service;

use de\mvo\Date;
use de\mvo\model\exception\DuplicateEntryException;
use de\mvo\model\permissions\GroupList;
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

            if ($user === null) {
                throw new NotFoundException;
            }
        } else {
            $user = null;
        }

        return TwigRenderer::render("admin/usermanagement/edit", array
        (
            "user" => $user
        ));
    }

    public function getProfilePicturePage()
    {
        $user = User::getById($this->params->id);

        if ($user === null) {
            throw new NotFoundException;
        }

        return TwigRenderer::render("admin/usermanagement/profile-picture", array
        (
            "user" => $user
        ));
    }

    public function createUser()
    {
        $user = new User;

        self::setUserDataFromPostData($user);

        try {
            $user->save();
        } catch (DuplicateEntryException $exception) {
            return TwigRenderer::render("admin/usermanagement/edit", array
            (
                "user" => $user,
                "showDuplicateUsernameError" => true
            ));
        }

        GroupList::load()->save();

        header("Location: /internal/admin/usermanagement");
        return null;
    }

    public function updateUser()
    {
        $user = User::getById($this->params->id);

        if ($user === null) {
            throw new NotFoundException;
        }

        self::setUserDataFromPostData($user);

        try {
            $user->save();
        } catch (DuplicateEntryException $exception) {
            return TwigRenderer::render("admin/usermanagement/edit", array
            (
                "user" => $user,
                "showDuplicateUsernameError" => true
            ));
        }

        GroupList::load()->save();

        header("Location: /internal/admin/usermanagement");
        return null;
    }

    public function getPermissionGroupsTree()
    {
        header("Content-Type: application/json");
        return json_encode(GroupList::load());
    }

    private static function setUserDataFromPostData(User $user)
    {
        $user->username = $_POST["username"];
        $user->firstName = $_POST["firstName"];
        $user->lastName = $_POST["lastName"];
        $user->email = $_POST["email"];

        if (isset($_POST["enabled"])) {
            $user->enabled = (bool)$_POST["enabled"];
        } else {
            $user->enabled = false;
        }

        $birthDate = $_POST["birthDate"];
        if ($birthDate !== null and $birthDate !== "") {
            $user->birthDate = new Date($birthDate);
        }

        $user->setPermissionGroupsById(explode(",", $_POST["permissionGroups"]));
    }
}