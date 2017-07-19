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

    /**
     * Remove any user instance from this list.
     *
     * @param User $user The user to remove
     */
    public function removeUser(User $user)
    {
        $iterator = $this->getIterator();

        /**
         * @var $thisUser User
         */
        foreach ($iterator as $index => $thisUser) {
            if ($thisUser->isEqualTo($user)) {
                $iterator->offsetUnset($index);
            }
        }
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

        return $this;
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

    public function disabledUsers()
    {
        $users = new self;

        /**
         * @var $user User
         */
        foreach ($this as $user) {
            if ($user->enabled) {
                continue;
            }

            $users->append($user);
        }

        return $users;
    }

    public function sortByLastNameAndFirstName()
    {
        $this->uasort(function (User $user1, User $user2) {
            return $user1->compareByLastNameAndFirstName($user2);
        });

        return $this;
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

    public function sortByNextBirthdays()
    {
        $this->uasort(function (User $user1, User $user2) {
            $user1NextBirthday = $user1->nextBirthday();
            $user2NextBirthday = $user2->nextBirthday();

            if ($user1NextBirthday < $user2NextBirthday) {
                return -1;
            }

            if ($user1NextBirthday > $user2NextBirthday) {
                return 1;
            }

            return strcmp($user1->getFullName(), $user2->getFullName());
        });

        return $this;
    }

    function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}