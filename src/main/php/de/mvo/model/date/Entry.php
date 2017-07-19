<?php
namespace de\mvo\model\date;

use de\mvo\Database;
use de\mvo\Date;
use PDO;

class Entry
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var Date
     */
    public $startDate;
    /**
     * @var Date
     */
    public $endDate;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;
    /**
     * @var Location
     */
    public $location;
    /**
     * @var bool
     */
    public $highlight = false;
    /**
     * @var bool
     */
    public $isPublic = false;
    /**
     * @var Groups
     */
    public $groups;
    /**
     * @var int
     */
    private $locationId;

    public function __construct()
    {
        $this->groups = new Groups;

        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->highlight = (bool)$this->highlight;
        $this->isPublic = (bool)$this->isPublic;
        $this->startDate = new Date($this->startDate);

        if ($this->endDate !== null) {
            $this->endDate = new Date($this->endDate);
        }

        if ($this->locationId !== null) {
            $this->locationId = (int)$this->locationId;
            $this->location = Location::getById($this->locationId);
        }

        if (!$this->isPublic) {
            $this->groups = Groups::getForEntry($this);
        }
    }

    /**
     * @param int $id
     *
     * @return Entry|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `dates`
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

    public function delete()
    {
        $query = Database::prepare("
            DELETE FROM `dates`
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":id" => $this->id
        ));
    }

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `dates`
                SET
                    `startDate` = :startDate,
                    `endDate` = :endDate,
                    `title` = :title,
                    `description` = :description,
                    `locationId` = :locationId,
                    `highlight` = :highlight,
                    `isPublic` = :isPublic
            ");
        } else {
            $query = Database::prepare("
                UPDATE `dates`
                SET
                    `startDate` = :startDate,
                    `endDate` = :endDate,
                    `title` = :title,
                    `description` = :description,
                    `locationId` = :locationId,
                    `highlight` = :highlight,
                    `isPublic` = :isPublic
                WHERE `id` = :id
            ");

            $query->bindValue(":id", $this->id, PDO::PARAM_INT);
        }

        $query->bindValue(":startDate", $this->startDate->toDatabase());
        $query->bindValue(":endDate", $this->endDate === null ? null : $this->endDate->toDatabase());
        $query->bindValue(":title", $this->title);
        $query->bindValue(":description", $this->description);
        $query->bindValue(":locationId", $this->location === null ? null : $this->location->id, PDO::PARAM_INT);
        $query->bindValue(":highlight", $this->highlight, PDO::PARAM_BOOL);
        $query->bindValue(":isPublic", $this->isPublic, PDO::PARAM_BOOL);

        $query->execute();

        if ($this->id === null) {
            $this->id = Database::lastInsertId();
        }

        $this->groups->save($this);
    }
}