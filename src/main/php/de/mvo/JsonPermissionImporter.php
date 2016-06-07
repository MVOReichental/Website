<?php
namespace de\mvo;

use de\mvo\model\permissions\Group;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\users\User;

class JsonPermissionImporter
{
	public static function readGroupList($list)
	{
		$groupList = new GroupList;

		foreach ($list as $group)
		{
			$groupList->append(self::readGroup($group));
		}

		return $groupList;
	}

	public static function readGroup($sourceGroup)
	{
		$group = new Group;
		$group->title = $sourceGroup->title;

		if (isset($sourceGroup->permissions))
		{
			foreach ($sourceGroup->permissions as $permission)
			{
				$group->permissions->append($permission);
			}
		}

		if (isset($sourceGroup->users))
		{
			foreach ($sourceGroup->users as $userId)
			{
				$group->addUser(User::getById($userId));
			}
		}

		if (isset($sourceGroup->subGroups))
		{
			$group->subGroups = self::readGroupList($sourceGroup->subGroups);
		}

		return $group;
	}
}