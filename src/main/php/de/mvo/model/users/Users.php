<?php
namespace de\mvo\model\users;

use ArrayObject;
use de\mvo\Database;
use JsonSerializable;

class Users extends ArrayObject implements JsonSerializable
{
    public function hasUser(User $user)
    {
        // We can't use in_array() here because we have to compare the id property instead of the whole object.

        /**
         * @var $thisUser User
         */
        foreach ($this as $thisUser) {
            if ($thisUser->isEqualTo($user)) {
                return true;
            }
        }

        return false;
    }

    public function addAll(Users $users)
    {
        foreach ($users as $user) {
            $this->append($user);
        }
    }

    public function makeUnique()
    {
        $this->exchangeArray(array_unique((array)$this));
    }

    public function enabledUsers()
    {
        $users = new self;

        /**
         * @var $user User
         */
        foreach ($this as $user) {
            if (!$user->enabled) {
                continue;
            }

            $users->append($user);
        }

        return $users;
    }

    public static function getAll()
    {
        $users = new self;

        $query = Database::query("SELECT * FROM `users`");

        while ($user = $query->fetchObject(User::class)) {
            $users->append($user);
        }

        return $users;
    }

    function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}