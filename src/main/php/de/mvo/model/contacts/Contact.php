<?php
namespace de\mvo\model\contacts;

class Contact
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $category;
	/**
	 * @var string
	 */
	public $value;

	public function __construct()
	{
		$this->id = (int) $this->id;
	}
}