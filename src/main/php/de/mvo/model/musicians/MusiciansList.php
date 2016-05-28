<?php
namespace de\mvo\model\musicians;

use ArrayObject;
use de\mvo\model\permissions\Group;
use de\mvo\model\permissions\GroupList;

class MusiciansList extends ArrayObject
{
	public function __construct()
	{
		$rootGroup = GroupList::load()->getGroupByPermission("groups.musicians");
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