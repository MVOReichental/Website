<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use de\mvo\Database;

class AlbumList extends ArrayObject
{
	public function __construct($year)
	{
		$query = Database::prepare("
			SELECT *
			FROM `picturealbums`
			WHERE `year` = :year AND `published` AND `isPublic`
		");

		$query->execute(array
		(
			":year" => $year
		));

		while ($album = $query->fetchObject(Album::class))
		{
			$this->append($album);
		}
	}
}