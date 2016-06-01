<?php
namespace de\mvo\model\users;

use ArrayObject;

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
			if ($thisUser->isEqualTo($user))
			{
				return true;
			}
		}

		return false;
	}
}