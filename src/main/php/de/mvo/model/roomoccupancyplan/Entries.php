<?php
namespace de\mvo\model\roomoccupancyplan;

use ArrayObject;
use de\mvo\Database;
use de\mvo\Date;

class Entries extends ArrayObject
{
    public static function getInRange(Date $startDate, Date $endDate)
    {
        $query = Database::prepare("
            SELECT *
            FROM `roomoccupancyplan`
            WHERE
              `date` BETWEEN :startDate AND :endDate OR
              (
                `repeatTillDate` IS NULL AND
                `repeatWeekly` AND
                `date` <= :endDate
              ) OR
              (
                `repeatTillDate` IS NOT NULL AND
                `repeatTillDate` >= :startDate AND
                `date` <= :endDate
              )
        ");

        $query->execute(array
        (
            ":startDate" => $startDate->format("c"),
            ":endDate" => $endDate->format("c")
        ));

        $entries = new self;

        while ($entry = $query->fetchObject(Entry::class)) {
            $entries->append($entry);
        }

        return $entries;
    }
}