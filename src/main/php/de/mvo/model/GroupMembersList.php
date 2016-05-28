<?php
namespace de\mvo\model;

use ArrayObject;
use de\mvo\model\permissions\Group;
use de\mvo\model\permissions\GroupList;

class GroupMembersList extends ArrayObject
{
	public function __construct($group)
	{
		$rootGroup = GroupList::load()->getGroupByPermission("groups." . $group);
		if ($rootGroup === null)
		{
			return;
		}

		/**
		 * @var $group Group
		 */
		foreach ($rootGroup->subGroups as $group)
		{
			$this->append($group);
		}
	}
}