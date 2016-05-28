<?php
namespace de\mvo\model\permissions;

use ArrayObject;
use de\mvo\model\User;

class Users extends ArrayObject
{
	public function hasUser(User $user)
	{
		// We can't use in_array() here because we have to compare the id property instead of the whole object.

		/**
		 * @var $thisUser User
		 */
		foreach ($this as $thisUser)
		{
			if ($thisUser->id == $user->id)
			{
				return true;
			}
		}

		return false;
	}
}