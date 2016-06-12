<?php
namespace de\mvo\model\date;

use ArrayObject;
use DateInterval;
use de\mvo\Database;
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
	 * @var string
	 */
	public $description;
	/**
	 * @var Location
	 */
	public $location;
	/**
	 * @var bool
	 */
	public $highlight;
	/**
	 * @var bool
	 */
	public $isPublic;
	/**
	 * @var ArrayObject
	 */
	public $groups;
	/**
	 * @var int
	 */
	private $locationId;

	public function __construct()
	{
		$this->id = (int) $this->id;
		$this->highlight  = (bool) $this->highlight;
		$this->isPublic  = (bool) $this->isPublic;
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

		$this->groups = new ArrayObject;

		if (!$this->isPublic)
		{
			$query = Database::prepare("
				SELECT `name`
				FROM `dategroups`
				WHERE `dateId` = :dateId
			");

			$query->execute(array
			(
				":dateId" => $this->id
			));

			while ($group = $query->fetchColumn(0))
			{
				$this->groups->append($group);
			}
		}
	}
}