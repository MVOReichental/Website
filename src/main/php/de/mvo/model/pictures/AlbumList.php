<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use de\mvo\Database;
use PDO;
use PDOStatement;

class AlbumList extends ArrayObject
{
	private static function executeQuery(PDOStatement $query)
	{
		$query->execute();

		$list = new self;

		while ($album = $query->fetchObject(Album::class))
		{
			$list->append($album);
		}

		return $list;
	}

	public static function getForYear($year)
	{
		$query = Database::prepare("
			SELECT *
			FROM `picturealbums`
			WHERE `year` = :year AND `published` AND `isPublic`
		");

		$query->bindValue(":year", $year);

		return self::executeQuery($query);
	}

	public static function getLatest($limit)
	{
		$query = Database::prepare("
			SELECT *
			FROM `picturealbums`
			WHERE `published` AND `isPublic`
			ORDER BY `date` DESC
			LIMIT :limit
		");

		$query->bindValue(":limit", $limit, PDO::PARAM_INT);

		return self::executeQuery($query);
	}
}