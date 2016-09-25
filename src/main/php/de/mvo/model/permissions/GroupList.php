<?php
namespace de\mvo\model\permissions;

use ArrayObject;
use de\mvo\model\users\User;
use JsonSerializable;
use UnexpectedValueException;

class GroupList extends ArrayObject implements JsonSerializable
{
    /**
     * @var GroupList
     */
    private static $root;

    /**
     * Load all permissions from file (if not already loaded) and return root GroupList object
     *
     * @return GroupList The root GroupList
     */
    public static function load()
    {
        if (self::$root !== null) {
            return self::$root;
        }

        $filename = RESOURCES_ROOT . "/permissions.serialized";

        if (file_exists($filename)) {
            self::$root = unserialize(file_get_contents($filename));
        } else {
            self::$root = new self;
        }

        if (self::$root instanceof self) {
            return self::$root;
        }

        throw new UnexpectedValueException("Unserialized data is not of type GroupList");
    }

    /**
     * Save this GroupList to the permissions file.
     */
    public function save()
    {
        file_put_contents(RESOURCES_ROOT . "/permissions.serialized", serialize($this));
    }

    public static function loadFromArray(array $array)
    {
        $groupList = new self;

        foreach ($array as $group) {
            $groupList->append(Group::loadFromStdClass($group));
        }

        return $groupList;
    }

    /**
     * Get the first group matching the given permission string.
     *
     * @param string $permission The permission to search for
     * @param bool $requireExactMatch Whether an exact match is required, false if also the wildcard placeholder "*" and regular expressions (prefixed with "@") are allowed
     *
     * @return Group|null
     */
    public function getGroupByPermission($permission, $requireExactMatch = true)
    {
        /**
         * @var $group Group
         */
        foreach ($this as $group) {
            $foundGroup = $group->getGroupByPermission($permission, $requireExactMatch);
            if ($foundGroup !== null) {
                return $foundGroup;
            }
        }

        return null;
    }

    public function getPermissionsForUser(User $user)
    {
        $foundPermissions = false;

        $permissions = new Permissions;

        /**
         * @var $group Group
         */
        foreach ($this as $group) {
            $groupPermissions = $group->getPermissionsForUser($user);
            if ($groupPermissions === null) {
                continue;
            }

            $foundPermissions = true;

            $permissions->addAll($groupPermissions);
        }

        if ($foundPermissions) {
            $permissions->makeUnique();

            return $permissions;
        }

        return null;
    }

    function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}