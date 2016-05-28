<?php
namespace de\mvo\model\permissions;

use ArrayObject;

class Permissions extends ArrayObject
{
	public function hasPermission($permission)
	{
		return in_array($permission, (array) $this);
	}

	public function addAll(Permissions $permissions)
	{
		foreach ($permissions as $permission)
		{
			$this->append($permission);
		}
	}

	public function makeUnique()
	{
		$this->exchangeArray(array_unique((array) $this));
	}
}