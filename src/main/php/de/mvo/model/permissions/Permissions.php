<?php
namespace de\mvo\model\permissions;

use ArrayObject;
use JsonSerializable;

class Permissions extends ArrayObject implements JsonSerializable
{
	public function hasPermission($permission, $requireExactMatch = true)
	{
		foreach ($this as $thisPermission)
		{
			if ($thisPermission == $permission)
			{
				return true;
			}

			if ($requireExactMatch)
			{
				continue;
			}

			$wildcardIndex = strpos($permission, "*");
			if ($wildcardIndex !== false and substr($thisPermission, 0, $wildcardIndex) == substr($permission, 0, $wildcardIndex))
			{
				return true;
			}

			if ($permission[0] == "@" and preg_match(substr($permission, 1), $thisPermission))
			{
				return true;
			}
		}

		return false;
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

	function jsonSerialize()
	{
		return $this->getArrayCopy();
	}
}