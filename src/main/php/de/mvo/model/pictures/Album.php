<?php
namespace de\mvo\model\pictures;

use de\mvo\Database;
use de\mvo\Date;

class Album
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
	 * @var string
	 */
	public $title;
	/**
	 * @var Date
	 */
	public $date;
	/**
	 * @var Picture
	 */
	public $cover;
	/**
	 * @var Pictures
	 */
	public $pictures;
	private $coverPictureId;

	public function __construct()
	{
		$this->id = (int) $this->id;
		$this->date = new Date($this->date);
		$this->pictures = new Pictures($this->id);
		$this->cover = $this->pictures->getPictureById($this->coverPictureId);
	}

	public function year()
	{
		return $this->date->format("Y");
	}

	public static function getById($id)
	{
		$query = Database::prepare("
			SELECT *
			FROM `picturealbums`
			WHERE `id` = :id AND `published` AND `isPublic`
		");

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

	public static function getByYearAndName($year, $name)
	{
		$query = Database::prepare("
			SELECT *
			FROM `picturealbums`
			WHERE `year` = :year AND `name` = :name AND `published` AND `isPublic`
		");

		$query->execute(array
		(
			":year" => $year,
			":name" => $name
		));

		if (!$query->rowCount())
		{
			return null;
		}

		return $query->fetchObject(self::class);
	}
}