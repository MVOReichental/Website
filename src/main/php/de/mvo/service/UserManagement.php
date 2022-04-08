<?php
namespace de\mvo\service;

use de\mvo\Date;
use de\mvo\model\exception\DuplicateEntryException;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use Twig\Error\Error;

class UserManagement extends AbstractService
{
    /**
     * @return string
     * @throws Error
     */
    public function getPage()
    {
        return TwigRenderer::render("admin/usermanagement/page", array
        (
            "users" => Users::getAll()->sortByLastNameAndFirstName()
        ));
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws Error
     */
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

    /**
     * @return string
     * @throws NotFoundException
     * @throws Error
     */
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

    /**
     * @return null|string
     * @throws Error
     */
    public function createUser()
    {
        $user = new User;

        return self::completeUserUpdate($user);
    }

    /**
     * @return null|string
     * @throws NotFoundException
     * @throws Error
     */
    public function updateUser()
    {
        $user = User::getById($this->params->id);

        if ($user === null) {
            throw new NotFoundException;
        }

        return self::completeUserUpdate($user);
    }

    public function getPermissionGroupsTree()
    {
        header("Content-Type: application/json");
        return json_encode(GroupList::load());
    }

    /**
     * @param User $user
     * @return null|string
     * @throws Error
     */
    private static function completeUserUpdate(User $user)
    {
        $user->username = $_POST["username"];
        $user->firstName = $_POST["firstName"];
        $user->lastName = $_POST["lastName"];
        $user->email = $_POST["email"];

        if (User::getCurrent()->isEqualTo($user)) {
            $user->enabled = true;
        } else {
            if (isset($_POST["enabled"])) {
                $user->enabled = (bool)$_POST["enabled"];
            } else {
                $user->enabled = false;
            }
        }

        $birthDate = $_POST["birthDate"];
        if ($birthDate !== null and $birthDate !== "") {
            $user->birthDate = new Date($birthDate);
        }

        $user->setPermissionGroupsById(explode(",", $_POST["permissionGroups"]));

        try {
            $user->save();
        } catch (DuplicateEntryException $exception) {
            return TwigRenderer::render("admin/usermanagement/edit", array
            (
                "user" => $user,
                "showDuplicateUsernameError" => true
            ));
        }

        if (isset($_POST["sendCredentials"]) and $_POST["sendCredentials"]) {
            $user->sendAccountCreatedMail();
        }

        GroupList::load()->save();

        header("Location: /internal/admin/usermanagement");
        return null;
    }
}