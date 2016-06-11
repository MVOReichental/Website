<?php
namespace de\mvo\model\date;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\User;
use PDO;

class DateList extends ArrayObject
{
	public function __construct(User $visibleForUser = null, $limit = 1000)
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

		/**
		 * @var $entry Entry
		 */
		while ($entry = $query->fetchObject(Entry::class))
		{
			if ($visibleForUser !== null)
			{
				if (!count($entry->groups))
				{
					$this->append($entry);
				}
				else
				{
					foreach ($entry->groups as $group)
					{
						if ($visibleForUser->hasPermission("dates.view." . $group))
						{
							$this->append($entry);
							break;
						}
					}
				}
			}
			else
			{
				if ($entry->isPublic)
				{
					$this->append($entry);
				}
			}
		}
	}
}