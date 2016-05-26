<?php
namespace de\mvo\model\date;

use ArrayObject;
use de\mvo\Database;

class DateList extends ArrayObject
{
	public function __construct()
	{
		$query = Database::query("
			SELECT *
			FROM `dates`
			WHERE `startDate` >= NOW() OR (`endDate` IS NOT NULL AND `endDate` > NOW())
			ORDER BY `startDate` ASC
		");

		while ($entry = $query->fetchObject(Entry::class))
		{
			$this->append($entry);
		}
	}
}