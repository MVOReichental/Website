<?php
namespace de\mvo\model\date;

use ArrayObject;
use de\mvo\Database;
use stdClass;

class Groups extends ArrayObject
{
	public static function getAll()
	{
		return array
		(
			"vorstandschaft" => "Vorstandschaft",
			"sonderaufgaben" => "Sonderaufgaben",
			"foerderverein" => "F&ouml;rderverein",
			"dirigentin" => "Dirigentin",
			"musiker" => "Musiker"
		);
	}

	public static function getForEntry(Entry $entry)
	{
		$groups = new self;

		$query = Database::prepare("
				SELECT `name`
				FROM `dategroups`
				WHERE `dateId` = :dateId
			");

		$query->execute(array
		(
			":dateId" => $entry->id
		));

		while ($group = $query->fetchColumn(0))
		{
			$groups->append($group);
		}

		return $groups;
	}

	public function save(Entry $entry)
	{
		$newGroups = $this->getArrayCopy();

		$query = Database::prepare("
			SELECT *
			FROM `dategroups`
			WHERE `dateId` = :dateId
		");

		$query->execute(array
		(
			":dateId" => $entry->id
		));

		$deleteQuery = Database::prepare("
			DELETE FROM `dategroups`
			WHERE `id` = :id
		");

		while ($row = $query->fetchObject(stdClass::class))
		{
			$index = array_search($row->group, $newGroups);
			if ($index === false)
			{
				$deleteQuery->execute(array
				(
					":id" => $row->id
				));
			}
			else
			{
				unset($newGroups[$index]);
			}
		}

		$query = Database::prepare("
			INSERT INTO `dategroups`
			SET
				`dateId` = :dateId,
				`name` = :name
		");

		foreach ($newGroups as $group)
		{
			$query->execute(array
			(
				":dateId" => $entry->id,
				":name" => $group
			));
		}

		$this->exchangeArray(self::getForEntry($entry)->getArrayCopy());
	}
}