<?php
namespace de\mvo\model\permissions;

use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use stdClass;

class Group
{
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var Users
	 */
	public $users;
	/**
	 * @var Permissions
	 */
	public $permissions;
	/**
	 * @var GroupList
	 */
	public $subGroups;

	public function __construct()
	{
		$this->users = new Users;
		$this->permissions = new Permissions;
		$this->subGroups = new GroupList;
	}

	public static function loadFromStdClass(stdClass $object)
	{
		$group = new self;

		if (isset($object->title))
		{
			$group->title = $object->title;
		}

		if (isset($object->permissions) and is_array($object->permissions))
		{
			foreach ($object->permissions as $permission)
			{
				$group->permissions->append($permission);
			}
		}

		if (isset($object->users) and is_array($object->users))
		{
			foreach ($object->users as $userId)
			{
				$group->addUser(User::getById($userId));
			}
		}

		if (isset($object->subGroups) and is_array($object->subGroups))
		{
			$group->subGroups = GroupList::loadFromArray($object->subGroups);
		}

		return $group;
	}

	public function getGroupByPermission($permission, $requireExactMatch = true)
	{
		if ($this->permissions->hasPermission($permission, $requireExactMatch))
		{
			return $this;
		}

		return $this->subGroups->getGroupByPermission($permission, $requireExactMatch);
	}

	public function addGroup(Group $group)
	{
		$this->subGroups->append($group);
	}

	public function addUser(User $user)
	{
		$this->users->append($user);
	}

	public function getPermissionsForUser(User $user)
	{
		$foundPermissions = false;

		$permissions = new Permissions;

		$subGroupPermission = $this->subGroups->getPermissionsForUser($user);
		if ($subGroupPermission !== null)
		{
			$permissions->addAll($subGroupPermission);
			$permissions->addAll($this->permissions);

			$foundPermissions = true;
		}

		if ($this->users->hasUser($user))
		{
			$permissions->addAll($this->permissions);

			$foundPermissions = true;
		}

		if ($foundPermissions)
		{
			$permissions->makeUnique();

			return $permissions;
		}

		return null;
	}

	public function getAllUsers()
	{
		$users = new Users;

		$users->addAll($this->users);

		/**
		 * @var $group Group
		 */
		foreach ($this->subGroups as $group)
		{
			$users->addAll($group->getAllUsers());
		}

		return $users;
	}
}