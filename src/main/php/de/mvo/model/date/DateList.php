<?php
namespace de\mvo\model\date;

use ArrayObject;
use de\mvo\Database;
use PDO;

class DateList extends ArrayObject
{
	public function __construct($limit = 1000)
	{
		$query = Database::prepare("
			SELECT *
			FROM `dates`
			WHERE `startDate` >= NOW() OR (`endDate` IS NOT NULL AND `endDate` > NOW())
			ORDER BY `startDate` ASC
			LIMIT :limit
		");

		$query->bindValue(":limit", $limit, PDO::PARAM_INT);

		$query->execute();

		while ($entry = $query->fetchObject(Entry::class))
		{
			$this->append($entry);
		}
	}
}