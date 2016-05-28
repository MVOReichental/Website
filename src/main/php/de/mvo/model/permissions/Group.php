<?php
namespace de\mvo\model\permissions;

use de\mvo\model\User;

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

	public function getGroupByPermission($permission)
	{
		if ($this->permissions->hasPermission($permission))
		{
			return $this;
		}

		return $this->subGroups->getGroupByPermission($permission);
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
}