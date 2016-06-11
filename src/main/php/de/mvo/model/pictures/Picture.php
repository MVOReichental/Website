<?php
namespace de\mvo\model\pictures;

use de\mvo\Database;

class Picture
{
	public $id;
	public $albumId;
	public $file;
	public $title;

	public function __construct()
	{
		$this->id = (int) $this->id;
		$this->albumId = (int) $this->albumId;
	}

	/**
	 * @param int $id
	 *
	 * @return Picture|null
	 */
	public static function getById($id)
	{
		$query = Database::prepare("
			SELECT *
			FROM `pictures`
			WHERE `id` = :id
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
}