<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use de\mvo\Database;

class YearList extends ArrayObject
{
	public function __construct()
	{
		$query = Database::query("
			SELECT `year`, `id` AS `coverAlbumId`
			FROM `picturealbums`
			WHERE `published` AND `isPublic` AND `isAlbumOfTheYear`
			GROUP BY `year`, `id`
		");

		while ($year = $query->fetchObject(Year::class))
		{
			$this->append($year);
		}
	}
}