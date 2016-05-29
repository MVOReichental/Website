<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use de\mvo\Database;
use PDO;

class LatestAlbumsList extends ArrayObject
{
	public function __construct($count)
	{
		$query = Database::prepare("
			SELECT *
			FROM `picturealbums`
			WHERE `published` AND `isPublic`
			ORDER BY `date` DESC
			LIMIT :limit
		");

		$query->bindValue(":limit", $count, PDO::PARAM_INT);

		$query->execute();

		while ($album = $query->fetchObject(Album::class))
		{
			$this->append($album);
		}
	}
}