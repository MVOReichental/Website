<?php
namespace App\Controller;

use App\Controller\exception\NotFoundException;
use App\Date;
use App\Entity\contacts\Contact;
use App\Entity\contacts\Contacts;
use App\Entity\exception\DuplicateEntryException;
use App\Entity\permissions\GroupList;
use App\Entity\users\User;
use App\Entity\users\Users;
use App\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class UserManagement extends AbstractController
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
            "user" => $user,
            "contactTypes" => Contact::TYPE_TILES,
            "contactCategories" => Contact::CATEGORY_TITLES,
            "showSuccessMessage" => isset($_GET["saved"]),
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

        $oldContacts = Contacts::forUser($user);
        $newContacts = new Contacts;

        foreach ($_POST["contactValue"] as $index => $value) {
            if (!$value) {
                continue;
            }

            if (isset($_POST["contactId"][$index])) {
                $contact = Contact::getById($_POST["contactId"][$index]);
            } else {
                $contact = new Contact;

                $contact->user = $user;
            }

            if (!isset($_POST["contactType"][$index]) or !isset($_POST["contactCategory"][$index])) {
                http_response_code(400);
                return null;
            }

            $contact->type = $_POST["contactType"][$index];
            $contact->category = $_POST["contactCategory"][$index];
            $contact->value = $value;

            $contact->save();

            $newContacts->append($contact);
        }

        // Delete removed contacts
        foreach ($oldContacts as $contact) {
            foreach ($newContacts as $newContact) {
                if ($newContact->id == $contact->id) {
                    continue 2;
                }
            }

            $contact->remove();
        }

        if (isset($_POST["sendCredentials"]) and $_POST["sendCredentials"]) {
            $user->sendAccountCreatedMail();
        }

        GroupList::load()->save();

        header(sprintf("Location: /internal/admin/usermanagement/user/%d?saved", $user->id));
        return null;
    }
}