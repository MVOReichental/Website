<?php
namespace de\mvo\model\date;

use DateInterval;
use de\mvo\Date;

class Entry
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var Date
	 */
	public $startDate;
	/**
	 * @var Date
	 */
	public $endDate;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var Location
	 */
	public $location;
	/**
	 * @var int
	 */
	private $locationId;

	public function __construct()
	{
		$this->id = (int) $this->id;
		$this->startDate = new Date($this->startDate);

		if ($this->endDate !== null)
		{
			$this->endDate = new Date($this->endDate);
		}

		if ($this->locationId !== null)
		{
			$this->locationId = (int) $this->locationId;
			$this->location = Location::getById($this->locationId);
		}
	}
}