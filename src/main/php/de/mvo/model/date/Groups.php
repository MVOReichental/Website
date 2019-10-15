<?php
namespace de\mvo\model\date;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\Groups as UserGroups;
use PDO;
use stdClass;

class Groups extends ArrayObject
{
    public static function getForEntry(Entry $entry)
    {
        $groups = new self;

        $query = Database::prepare("
            SELECT `name`
            FROM `dategroups`
            WHERE `dateId` = :dateId
        ");

        $query->bindValue(":dateId", $entry->id, PDO::PARAM_INT);

        $query->execute();

        while ($group = $query->fetchColumn(0)) {
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

        $query->bindValue(":dateId", $entry->id, PDO::PARAM_INT);

        $query->execute();

        $deleteQuery = Database::prepare("
            DELETE FROM `dategroups`
            WHERE `id` = :id
        ");

        while ($row = $query->fetchObject(stdClass::class)) {
            $index = array_search($row->group, $newGroups);
            if ($index === false) {
                $deleteQuery->bindValue(":id", $row->id, PDO::PARAM_INT);

                $deleteQuery->execute();
            } else {
                unset($newGroups[$index]);
            }
        }

        $query = Database::prepare("
            INSERT INTO `dategroups`
            SET
                `dateId` = :dateId,
                `name` = :name
        ");

        foreach ($newGroups as $group) {
            $query->bindValue(":dateId", $entry->id, PDO::PARAM_INT);
            $query->bindValue(":name", $group);

            $query->execute();
        }

        $this->exchangeArray(self::getForEntry($entry)->getArrayCopy());
    }

    public function has($group)
    {
        return in_array($group, $this->getArrayCopy());
    }

    public function isAnyIn(Groups $groups)
    {
        foreach ($this as $group) {
            if ($groups->has($group)) {
                return true;
            }
        }

        return false;
    }

    public function getTitles()
    {
        $allGroups = UserGroups::getAll();
        $titles = array();

        foreach ($this as $group) {
            $titles[] = $allGroups[$group];
        }

        return $titles;
    }
}