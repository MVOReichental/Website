<?php
namespace de\mvo\service;

use de\mvo\model\contacts\Contact;
use de\mvo\model\contacts\Contacts;
use de\mvo\model\users\User;
use de\mvo\service\exception\LoginException;
use de\mvo\TwigRenderer;
use Exception;
use Kelunik\TwoFactor\Oath;
use ParagonIE\ConstantTime\Base32;
use PDOException;
use Twig_Error;

class Account extends AbstractService
{
    const UPDATE_PASSWORD_OK = "ok";
    const UPDATE_PASSWORD_INVALID = "invalid";
    const UPDATE_PASSWORD_POLICY_ERROR = "policy_error";

    const UPDATE_USERNAME_MIN_LENGTH = "min_length";
    const UPDATE_USERNAME_ALREADY_IN_USE = "username_already_in_use";
    const UPDATE_USERNAME_OK = "ok";

    const UPDATE_EMAIL_INVALID_PASSWORD = "invalid_password";
    const UPDATE_EMAIL_UNCHANGED = "unchanged";
    const UPDATE_EMAIL_INVALID = "invalid";
    const UPDATE_EMAIL_OK = "ok";

    const UPDATE_CONTACT_INVALID_ID = "invalid_id";
    const UPDATE_CONTACT_OK = "ok";

    const UPDATE_PROFILE_OK = "ok";

    public static function getSettingsPages()
    {
        return array
        (
            "profile" => array
            (
                "title" => "Profil"
            ),
            "account" => array
            (
                "title" => "Account"
            ),
            "email" => array
            (
                "title" => "Email-Adresse"
            ),
            "contact" => array
            (
                "title" => "Kontakt"
            )
        );
    }

    /**
     * @return null
     * @throws Exception
     * @throws LoginException
     */
    public function login()
    {
        if (isset($_POST["username"]) and isset($_POST["password"])) {
            $user = User::getByUsername($_POST["username"]);

            if ($user === null or !$user->enabled or !$user->validatePassword($_POST["password"])) {
                throw new LoginException(LoginException::INVALID_CREDENTIALS);
            }

            if ($user->has2FA()) {
                $_SESSION["2faLoginUserId"] = $user->id;

                throw new LoginException(LoginException::REQUIRE_2FA_TOKEN);
            }
        } elseif (isset($_SESSION["2faLoginUserId"]) and isset($_POST["2fa-token"])) {
            $user = User::getById($_SESSION["2faLoginUserId"]);
            if ($user === null) {
                throw new Exception("User not found");
            }

            if (!$user->validateTotp($_POST["2fa-token"])) {
                throw new LoginException(LoginException::INVALID_2FA_TOKEN);
            }

            unset($_SESSION["2faLoginUserId"]);
        } else {
            throw new LoginException(LoginException::UNKNOWN_ERROR);
        }

        $user->doLogin();

        if (isset($_GET["redirect"]) and $_GET["redirect"] != "") {
            header("Location: " . $_GET["redirect"], true, 302);
        } else {
            header("Location: /internal", true, 302);
        }

        return null;
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function logout()
    {
        User::logout();

        return TwigRenderer::render("account/logout");
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function resetPassword()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = User::getByUsername($_POST["username"]);
            if ($user === null) {
                return TwigRenderer::render("account/reset-password/request", array
                (
                    "errorMessage" => "Der Benutzer existiert nicht!"
                ));
            }

            $user->sendPasswordResetMail();

            return TwigRenderer::render("account/reset-password/send-ok");
        } else {
            return TwigRenderer::render("account/reset-password/request");
        }
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function confirmResetPassword()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = User::getById($_GET["id"]);

            if ($user->checkPasswordResetKey($_GET["key"])) {
                $newPassword = $_POST["password"];
                if (User::checkPasswordPolicy($newPassword)) {
                    $user->setPassword($newPassword);

                    return TwigRenderer::render("account/reset-password/changed");
                } else {
                    return TwigRenderer::render("account/reset-password/confirm", array
                    (
                        "passwordPolicyError" => true
                    ));
                }
            }
        } else {
            if (isset($_GET["id"]) and isset($_GET["key"])) {
                $user = User::getById($_GET["id"]);

                if ($user->checkPasswordResetKey($_GET["key"])) {
                    return TwigRenderer::render("account/reset-password/confirm");
                }
            }
        }

        return TwigRenderer::render("account/reset-password/confirm-error");
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function confirmEmailChange()
    {
        $user = User::getById($_GET["id"]);

        if (!$user->checkEmailChangeKey($_GET["key"])) {
            return TwigRenderer::render("account/change-email/invalid-request");
        }

        $user->setEmail($user->newEmail);

        return TwigRenderer::render("account/change-email/confirm", array
        (
            "email" => $user->email
        ));
    }

    /**
     * @param null $updateStatus
     * @return string
     * @throws Twig_Error
     */
    public function showSettings($updateStatus = null)
    {
        $user = User::getCurrent();

        $pages = self::getSettingsPages();

        $activePage = null;

        foreach ($pages as $name => &$page) {
            $page["name"] = $name;

            if ($this->params->page == $page["name"]) {
                $page["active"] = true;

                $activePage = $page;
            } else {
                $page["active"] = false;
            }
        }

        return TwigRenderer::render("account/settings/" . $activePage["name"], array
        (
            "classInstance" => $this,
            "update" => $updateStatus,
            "pages" => array_values($pages),
            "title" => $activePage["title"],
            "activePage" => $activePage,
            "user" => $user,
            "contactTypes" => Contact::TYPE_TILES,
            "contactCategories" => Contact::CATEGORY_TITLES
        ));
    }

    private function updateUsername()
    {
        if (!isset($_POST["username"])) {
            http_response_code(400);
            return null;
        }

        $username = trim($_POST["username"]);

        if (strlen($username) < 3) {
            return self::UPDATE_USERNAME_MIN_LENGTH;
        }

        try {
            User::getCurrent()->setUsername($username);
        } catch (PDOException $exception) {
            // Duplicate username
            if ($exception->errorInfo[1] == 1062) {
                return self::UPDATE_USERNAME_ALREADY_IN_USE;
            } else {
                throw $exception;
            }
        }

        return self::UPDATE_USERNAME_OK;
    }

    private function updateProfile()
    {
        if (!isset($_POST["firstName"]) or !isset($_POST["lastName"])) {
            http_response_code(400);
            return null;
        }

        User::getCurrent()->setName($_POST["firstName"], $_POST["lastName"]);

        return self::UPDATE_PROFILE_OK;
    }

    private function updatePassword()
    {
        if (!isset($_POST["currentPassword"]) or !isset($_POST["newPassword"])) {
            http_response_code(400);
            return null;
        }

        $user = User::getCurrent();

        if (!$user->validatePassword($_POST["currentPassword"])) {
            return self::UPDATE_PASSWORD_INVALID;
        }

        $newPassword = $_POST["newPassword"];
        if (!User::checkPasswordPolicy($newPassword)) {
            return self::UPDATE_PASSWORD_POLICY_ERROR;
        }

        $user->setPassword($newPassword);

        return self::UPDATE_PASSWORD_OK;
    }

    /**
     * @return string|null
     * @throws Twig_Error
     */
    private function updateEmailAddress()
    {
        if (!isset($_POST["password"]) or !isset($_POST["email"])) {
            http_response_code(400);
            return null;
        }

        $user = User::getCurrent();

        if (!$user->validatePassword($_POST["password"])) {
            return self::UPDATE_EMAIL_INVALID_PASSWORD;
        }

        if ($user->email === $_POST["email"]) {
            return self::UPDATE_EMAIL_UNCHANGED;
        }

        if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) === false) {
            return self::UPDATE_EMAIL_INVALID;
        }

        $user->setNewEmailAddress($_POST["email"]);
        $user->sendEmailChangeMail();

        return self::UPDATE_EMAIL_OK;
    }

    private function updateContacts()
    {
        if (!isset($_POST["value"]) or !is_array($_POST["value"])) {
            http_response_code(400);
            return null;
        }

        $user = User::getCurrent();

        $oldContacts = Contacts::forUser($user);
        $newContacts = new Contacts;

        foreach ($_POST["value"] as $index => $value) {
            if (!$value) {
                continue;
            }

            if (isset($_POST["id"][$index])) {
                $contact = Contact::getById($_POST["id"][$index]);

                if ($contact === null or !$contact->user->isEqualTo($user)) {
                    return self::UPDATE_CONTACT_INVALID_ID;
                }
            } else {
                $contact = new Contact;

                $contact->user = $user;
            }

            if (!isset($_POST["type"][$index]) or !isset($_POST["category"][$index])) {
                http_response_code(400);
                return null;
            }

            $contact->type = $_POST["type"][$index];
            $contact->category = $_POST["category"][$index];
            $contact->value = $value;

            $contact->save();

            $newContacts->append($contact);
        }

        // Delete removed contacts
        foreach ($oldContacts as $contact) {
            foreach ($newContacts as $newContact) {
                if ($newContact->id = $contact) {
                    continue 2;
                }
            }

            $contact->remove();
        }

        return self::UPDATE_CONTACT_OK;
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function updateSettings()
    {
        $response = null;

        if (isset($_POST["form"])) {
            switch ($_POST["form"]) {
                case "username":
                    $response = $this->updateUsername();
                    break;
                case "profile":
                    $response = $this->updateProfile();
                    break;
                case "password":
                    $response = $this->updatePassword();
                    break;
                case "email":
                    $response = $this->updateEmailAddress();
                    break;
                case "contact":
                    $response = $this->updateContacts();
                    break;
                default:
                    http_response_code(400);
                    $response = null;
                    break;
            }
        } else {
            http_response_code(400);
        }

        return $this->showSettings(array
        (
            $_POST["form"] => $response
        ));
    }

    public function request2faKey()
    {
        if (!isset($_POST["password"])) {
            http_response_code(400);
            return null;
        }

        $user = User::getCurrent();

        if (!$user->validatePassword($_POST["password"])) {
            http_response_code(401);
            echo "INVALID_PASSWORD";
            return null;
        }

        $oath = new Oath;

        $key = $oath->generateKey();

        $uri = $oath->getUri($key, "MVO", $user->username);

        $secret = Base32::encode($key);

        $_SESSION["2faKey"] = $secret;

        header("Content-Type: application/json");

        echo json_encode(array
        (
            "uri" => $uri,
            "secret" => $secret
        ));

        return null;
    }

    public function enable2fa()
    {
        if (!isset($_SESSION["2faKey"]) or !isset($_POST["token"])) {
            http_response_code(400);
            return null;
        }

        $user = User::getCurrent();

        if (!$user->validateTotp($_POST["token"], $_SESSION["2faKey"])) {
            http_response_code(400);
            echo "INVALID_TOKEN";
            return null;
        }

        $user->setTotpKey($_SESSION["2faKey"]);

        return null;
    }

    public function disable2fa()
    {
        if (!isset($_POST["password"])) {
            http_response_code(400);
            return null;
        }

        $user = User::getCurrent();

        if (!$user->validatePassword($_POST["password"])) {
            http_response_code(401);
            echo "INVALID_PASSWORD";
            return null;
        }

        $user->setTotpKey(null);

        return null;
    }
}