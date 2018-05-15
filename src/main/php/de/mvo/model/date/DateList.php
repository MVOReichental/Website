<?php
namespace de\mvo\model\date;

use ArrayObject;
use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\users\User;
use PDO;

class DateList extends ArrayObject
{
    public static function get($limit = 1000)
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

        $list = new self;

        /**
         * @var $entry Entry
         */
        while ($entry = $query->fetchObject(Entry::class)) {
            $list->append($entry);
        }

        return $list;
    }

    public static function getBetween(Date $start, Date $end)
    {
        $query = Database::prepare("
            SELECT *
            FROM `dates`
            WHERE `startDate` >= :startDate AND `startDate` <= :endDate
            ORDER BY `startDate` ASC
        ");

        $query->bindValue(":startDate", $start->toDatabase());
        $query->bindValue(":endDate", $end->toDatabase());

        $query->execute();

        $list = new self;

        /**
         * @var $entry Entry
         */
        while ($entry = $query->fetchObject(Entry::class)) {
            $list->append($entry);
        }

        return $list;
    }

    public static function getAll()
    {
        $query = Database::query("
            SELECT *
            FROM `dates`
        ");

        $list = new self;

        /**
         * @var $entry Entry
         */
        while ($entry = $query->fetchObject(Entry::class)) {
            $list->append($entry);
        }

        return $list;
    }

    public static function getAllPublic()
    {
        $query = Database::query("
            SELECT *
            FROM `dates`
            WHERE `isPublic`
        ");

        $list = new self;

        /**
         * @var $entry Entry
         */
        while ($entry = $query->fetchObject(Entry::class)) {
            $list->append($entry);
        }

        return $list;
    }

    public function visibleForUser(User $user)
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($entry->isPublic) {
                $list->append($entry);
                continue;
            }

            foreach ($entry->groups as $group) {
                if ($user->hasPermission("dates.view." . $group)) {
                    $list->append($entry);
                    break;
                }
            }
        }

        return $list;
    }

    public function publiclyVisible()
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($entry->isPublic) {
                $list->append($entry);
            }
        }

        return $list;
    }

    public function startingAt(Date $date)
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($entry->endDate === null) {
                $endDate = clone $entry->startDate;
                $endDate->setTime(23, 59, 59);
            } else {
                $endDate = $entry->endDate;
            }

            if ($endDate >= $date) {
                $list->append($entry);
                continue;
            }
        }

        return $list;
    }

    public function withYear(int $year)
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ((int)$entry->startDate->format("Y") === $year) {
                $list->append($entry);
            }
        }

        return $list;
    }

    public function getYears()
    {
        $years = array();

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            $years[] = (int)$entry->startDate->format("Y");
        }

        return array_unique($years);
    }

    public function getInGroups(Groups $groups, bool $includePublic)
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($includePublic and $entry->isPublic) {
                $list->append($entry);
                continue;
            }

            if (!$entry->groups->isAnyIn($groups)) {
                continue;
            }

            $list->append($entry);
        }

        return $list;
    }
}