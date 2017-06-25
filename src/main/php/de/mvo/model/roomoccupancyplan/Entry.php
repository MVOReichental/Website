<?php
namespace de\mvo\model\roomoccupancyplan;

use de\mvo\Database;
use de\mvo\Date;

class Entry
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $startTime;
    /**
     * @var string
     */
    public $endTime;
    /**
     * @var Date
     */
    public $date;
    /**
     * @var Date|null
     */
    public $repeatTillDate;
    /**
     * @var bool
     */
    public $repeatWeekly;
    /**
     * @var string
     */
    public $title;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->repeatWeekly = (bool)$this->repeatWeekly;
        $this->date = new Date($this->date);

        if ($this->repeatTillDate !== null) {
            $this->repeatTillDate = new Date($this->repeatTillDate);
        }
    }

    /**
     * @param int $id
     * @return Entry|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `roomoccupancyplan`
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":id" => $id
        ));

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `roomoccupancyplan`
                SET
                    `startTime` = :startTime,
                    `endTime` = :endTime,
                    `date`  = :date,
                    `repeatTillDate` = :repeatTillDate,
                    `repeatWeekly` = :repeatWeekly,
                    `title` = :title
            ");

            $query->execute(array
            (
                ":startTime" => $this->startTime,
                ":endTime" => $this->endTime,
                ":date" => $this->date->toDatabase(),
                ":repeatTillDate" => $this->repeatTillDate === null ? null : $this->repeatTillDate->toDatabase(),
                ":repeatWeekly" => (int)$this->repeatWeekly,
                ":title" => $this->title
            ));

            $this->id = (int)Database::lastInsertId();
        } else {
            $query = Database::prepare("
                UPDATE `roomoccupancyplan`
                SET
                    `startTime` = :startTime,
                    `endTime` = :endTime,
                    `date`  = :date,
                    `repeatTillDate` = :repeatTillDate,
                    `repeatWeekly` = :repeatWeekly,
                    `title` = :title
                WHERE `id` = :id
            ");

            $query->execute(array
            (
                ":startTime" => $this->startTime,
                ":endTime" => $this->endTime,
                ":date" => $this->date->toDatabase(),
                ":repeatTillDate" => $this->repeatTillDate === null ? null : $this->repeatTillDate->toDatabase(),
                ":repeatWeekly" => (int)$this->repeatWeekly,
                ":title" => $this->title,
                ":id" => $this->id
            ));
        }
    }
}