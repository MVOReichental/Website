<?php
namespace de\mvo\model\permissions;

use ArrayObject;
use de\mvo\model\users\User;
use UnexpectedValueException;

class GroupList extends ArrayObject
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
		if (self::$root !== null)
		{
			return self::$root;
		}

		$filename = RESOURCES_ROOT . "/permissions.serialized";

		if (file_exists($filename))
		{
			self::$root = unserialize(file_get_contents(RESOURCES_ROOT . "/permissions.serialized"));
		}
		else
		{
			self::$root = new GroupList;
		}

		if (self::$root instanceof GroupList)
		{
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

	/**
	 * Get the first group matching the given permission string.
	 *
	 * @param string $permission The permission to search for
	 *
	 * @return Group|null
	 */
	public function getGroupByPermission($permission)
	{
		/**
		 * @var $group Group
		 */
		foreach ($this as $group)
		{
			$foundGroup = $group->getGroupByPermission($permission);
			if ($foundGroup !== null)
			{
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
		foreach ($this as $group)
		{
			$groupPermissions = $group->getPermissionsForUser($user);
			if ($groupPermissions === null)
			{
				continue;
			}

			$foundPermissions = true;

			$permissions->addAll($groupPermissions);
		}

		if ($foundPermissions)
		{
			$permissions->makeUnique();

			return $permissions;
		}

		return null;
	}
}