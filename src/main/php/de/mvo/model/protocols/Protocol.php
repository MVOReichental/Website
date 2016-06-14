<?php
namespace de\mvo\model\protocols;

use de\mvo\model\uploads\Upload;
use de\mvo\model\users\User;

class Protocol
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var Upload|null
	 */
	public $upload;
	/**
	 * @var Groups
	 */
	public $groups;
	/**
	 * @var int
	 */
	private $uploadId;

	public function __construct()
	{
		if ($this->id === null)
		{
			return;
		}

		$this->id = (int) $this->id;
		$this->upload = Upload::getById($this->uploadId);
		$this->groups = Groups::getForProtocol($this);
	}

	public function isVisibleForUser(User $user)
	{
		foreach ($this->groups as $group)
		{
			if ($user->hasPermission("protocols.view." . $group))
			{
				return true;
			}
		}

		return false;
	}
}