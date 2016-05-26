<?php
namespace de\mvo\model\date;

use de\mvo\Database;

class Location
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var float
	 */
	public $latitude;
	/**
	 * @var float
	 */
	public $longitude;

	public function __construct()
	{
		$this->id = (int) $this->id;

		if ($this->latitude !== null)
		{
			$this->latitude = (float) $this->latitude;
		}

		if ($this->longitude !== null)
		{
			$this->longitude = (float) $this->longitude;
		}
	}

	/**
	 * @param int $id
	 *
	 * @return Location|null
	 */
	public static function getById($id)
	{
		$query = Database::prepare("SELECT * FROM `locations` WHERE `id` = :id");

		$query->execute(array
		(
			":id" => $id
		));

		if (!$query->rowCount())
		{
			return null;
		}

		return $query->fetchObject(self::class);
	}
}