<?php
namespace de\mvo\model\users;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\mail\Message;
use de\mvo\mail\Sender;
use de\mvo\model\contacts\Contacts;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\permissions\Permissions;
use de\mvo\TwigRenderer;
use de\mvo\utils\Url;
use JsonSerializable;
use Kelunik\TwoFactor\Oath;
use PDOException;
use RuntimeException;
use UnexpectedValueException;

class User implements JsonSerializable
{
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string|null
     */
    private $resetPasswordKey;
    /**
     * @var Date|null
     */
    private $resetPasswordDate;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string|null
     */
    public $newEmail;
    /**
     * @var string|null
     */
    private $newEmailKey;
    /**
     * @var Date|null
     */
    private $newEmailDate;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var Date
     */
    public $birthDate;
    /**
     * @var Date
     */
    public $lastOnline;
    /**
     * @var bool
     */
    public $enabled;
    /**
     * @var string
     */
    private $totpKey;
    /**
     * @var Permissions|null|false The permissions of the user, null if not loaded yet, false if the user does not have any permission
     */
    private $permissions;
    /**
     * @var User
     */
    private static $currentUser;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->enabled = (bool)$this->enabled;

        if ($this->birthDate !== null) {
            $this->birthDate = new Date($this->birthDate);
        }

        if ($this->lastOnline !== null) {
            $this->lastOnline = new Date($this->lastOnline);
        }

        if ($this->resetPasswordDate !== null) {
            $this->resetPasswordDate = new Date($this->resetPasswordDate);
        }

        if ($this->newEmailDate !== null) {
            $this->newEmailDate = new Date($this->newEmailDate);
        }
    }

    public static function getCurrent()
    {
        if (self::$currentUser === null and isset($_SESSION["userId"])) {
            $user = self::getById($_SESSION["userId"]);

            if ($user === null or !$user->enabled) {
                unset($_SESSION["userId"]);
                $user = null;
            }

            if ($user !== null) {
                $user->doLogin();
            }

            self::$currentUser = $user;
        }

        return self::$currentUser;
    }

    public static function logout()
    {
        unset($_SESSION["userId"]);

        self::$currentUser = null;
    }

    /**
     * @param int $id
     *
     * @return User|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `users`
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":id" => $id
        ));

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    /**
     * @param string $username
     *
     * @return User|null
     */
    public static function getByUsername($username)
    {
        $query = Database::prepare("
            SELECT *
            FROM `users`
            WHERE `username` = :username
        ");

        $query->execute(array
        (
            ":username" => $username
        ));

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    public static function checkPasswordPolicy($password)
    {
        // Does not contain at least X characters
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return false;
        }

        // Does not contain any alphanumeric characters (A-Z or a-z)
        if (!preg_match("/[a-zA-Z]+/", $password)) {
            return false;
        }

        // Does not contain any numbers (0-9)
        if (!preg_match("/[0-9]+/", $password)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function validatePassword($password)
    {
        if (!password_verify($password, $this->password)) {
            return false;
        }

        if (password_needs_rehash($this->password, PASSWORD_DEFAULT)) {
            $this->setPassword($password);
        }

        return true;
    }

    public function setPassword($password)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);

        $query = Database::prepare("
            UPDATE `users`
            SET
                `password` = :password,
                `resetPasswordKey` = NULL,
                `resetPasswordDate` = NULL
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":password" => $password,
            ":id" => $this->id
        ));

        $this->password = $password;

        // For security reasons, reset resetPassword fields
        $this->resetPasswordKey = null;
        $this->resetPasswordDate = null;
    }

    public function sendPasswordResetMail()
    {
        $key = bin2hex(random_bytes(16));

        $query = Database::prepare("
            UPDATE `users`
            SET
                `resetPasswordKey` = :resetPasswordKey,
                `resetPasswordDate` = NOW()
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":resetPasswordKey" => $key,
            ":id" => $this->id
        ));

        $sender = new Sender;

        $message = new Message;

        $message->setTo($this->email, $this->getFullName());
        $message->setBody(TwigRenderer::render("account/reset-password/mail", array
        (
            "user" => $this,
            "url" => Url::getBaseUrl() . "/internal/reset-password/confirm?id=" . $this->id . "&key=" . $key
        )), "text/html");
        $message->setSubjectFromHtml($message->getBody());

        $sender->send($message);
    }

    public function checkPasswordResetKey($key)
    {
        if ($this->resetPasswordKey != $key) {
            return false;
        }

        // Key should only be valid for 24 hours (1 day)
        $now = new Date;
        if ($now->diff($this->resetPasswordDate)->days > 0) {
            return false;
        }

        return true;
    }

    public function setNewEmailAddress($newEmail)
    {
        $this->newEmail = $newEmail;

        $query = Database::prepare("
            UPDATE `users`
            SET `newEmail` = :newEmail
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":newEmail" => $newEmail,
            ":id" => $this->id
        ));
    }

    public function sendEmailChangeMail()
    {
        if ($this->newEmail === null) {
            throw new UnexpectedValueException("New email address not defined yet");
        }

        $key = bin2hex(random_bytes(16));

        $query = Database::prepare("
            UPDATE `users`
            SET
                `newEmailKey` = :newEmailKey,
                `newEmailDate` = NOW()
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":newEmailKey" => $key,
            ":id" => $this->id
        ));

        $sender = new Sender;

        $message = new Message;

        $message->setTo($this->newEmail, $this->getFullName());
        $message->setBody(TwigRenderer::render("account/change-email/confirm-mail", array
        (
            "user" => $this,
            "url" => Url::getBaseUrl() . "/internal/change-email/confirm?id=" . $this->id . "&key=" . $key
        )), "text/html");
        $message->setSubjectFromHtml($message->getBody());

        $sender->send($message);
    }

    public function checkEmailChangeKey($key)
    {
        if ($this->newEmailKey != $key) {
            return false;
        }

        // Key should only be valid for 24 hours (1 day)
        $now = new Date;
        if ($now->diff($this->newEmailDate)->days > 0) {
            return false;
        }

        return true;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        // For security reasons, reset newEmail fields
        $this->newEmail = null;
        $this->newEmailKey = null;
        $this->newEmailDate = null;

        $query = Database::prepare("
            UPDATE `users`
            SET
                `email` = :email,
                `newEmail` = NULL,
                `newEmailKey` = NULL,
                `newEmailDate` = NULL
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":email" => $email,
            ":id" => $this->id
        ));
    }

    public function setUsername($username)
    {
        $query = Database::prepare("
            UPDATE `users`
            SET `username` = :username
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":username" => $username,
            ":id" => $this->id
        ));

        $this->username = $username;
    }

    public function setName($firstName, $lastName)
    {
        $query = Database::prepare("
            UPDATE `users`
            SET
                `firstName` = :firstName,
                `lastName` = :lastName
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":firstName" => $firstName,
            ":lastName" => $lastName,
            ":id" => $this->id
        ));

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function hasPermission($permission)
    {
        if ($this->permissions === null) {
            $this->permissions = GroupList::load()->getPermissionsForUser($this);
            if ($this->permissions === null) {
                $this->permissions = false;
            }
        }

        if ($this->permissions === false) {
            return false;
        }

        if ($this->permissions->hasPermission("*")) {
            return true;
        }

        return $this->permissions->hasPermission($permission, false);
    }

    public function has2FA()
    {
        return $this->totpKey !== null;
    }

    public function setTotpKey($key)
    {
        $query = Database::prepare("
            UPDATE `users`
            SET `totpKey` = :totpKey
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":totpKey" => $key,
            ":id" => $this->id
        ));
    }

    public function validateTotp($token, $key = null)
    {
        if ($key === null) {
            $key = $this->totpKey;
        }

        $oath = new Oath;

        if (!$oath->verifyTotp($key, $token)) {
            return false;
        }

        // Cleanup token lock table
        Database::query("
            DELETE FROM `usedtotptokens`
            WHERE `date` < DATE_SUB(NOW(), INTERVAL 90 SECOND)
        ");

        $query = Database::prepare("
            SELECT `id` FROM `usedtotptokens`
            WHERE `userId` = :userId AND `token` = :token
        ");

        $query->execute(array
        (
            ":userId" => $this->id,
            ":token" => $token
        ));

        if ($query->rowCount()) {
            return false;
        }

        $query = Database::prepare("
            INSERT INTO `usedtotptokens`
            SET
                `userId` = :userId,
                `token` = :token,
                `date` = NOW()
        ");

        try {
            $query->execute(array
            (
                ":userId" => $this->id,
                ":token" => $token
            ));

            return true;
        } catch (PDOException $exception) {
            // Duplicate token
            if ($exception->errorInfo[1] == 1062) {
                return false;
            } else {
                throw $exception;
            }
        }
    }

    public function updateLastOnline()
    {
        $this->lastOnline = new Date;

        $query = Database::prepare("
            UPDATE `users`
            SET `lastOnline` = :date
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":date" => $this->lastOnline->format("c"),
            ":id" => $this->id
        ));
    }

    public function isEqualTo(User $user)
    {
        return ($this->id == $user->id);
    }

    public static function getProfilePicturePath($userId)
    {
        $filename = PROFILE_PICTURES_ROOT . "/" . $userId . ".jpg";
        if (!file_exists($filename)) {
            $filename = PROFILE_PICTURES_ROOT . "/default.jpg";
        }

        return $filename;
    }

    public function profilePictureHash()
    {
        return md5_file(self::getProfilePicturePath($this->id));
    }

    public function contacts()
    {
        return Contacts::forUser($this);
    }

    public function getFullName()
    {
        return $this->firstName . " " . $this->lastName;
    }

    public function doLogin()
    {
        $this->updateLastOnline();

        $_SESSION["userId"] = $this->id;
    }

    public function isOnline()
    {
        if ($this->lastOnline === null) {
            return false;
        }

        if ((new Date)->getTimestamp() - $this->lastOnline->getTimestamp() > 300) {
            return false;
        }

        return true;
    }

    public function __toString()
    {
        return (string)$this->id;
    }

    public function __sleep()
    {
        return array("id");
    }

    public function __wakeup()
    {
        $user = self::getById($this->id);
        if ($user === null) {
            throw new RuntimeException("Unable to load user '" . $this->id . "'");
        }

        foreach (get_object_vars($user) as $name => $value) {
            $this->{$name} = $value;
        }
    }

    function jsonSerialize()
    {
        return $this->id;
    }
}