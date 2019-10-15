<?php
namespace de\mvo\model\date;

use de\mvo\Database;
use PDO;

class Location
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var float
     */
    public $latitude;
    /**
     * @var float
     */
    public $longitude;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;

        if ($this->latitude !== null) {
            $this->latitude = (float)$this->latitude;
        }

        if ($this->longitude !== null) {
            $this->longitude = (float)$this->longitude;
        }
    }

    /**
     * @param int $id
     *
     * @return Location|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("SELECT * FROM `locations` WHERE `id` = :id");

        $query->bindValue(":id", $id, PDO::PARAM_INT);

        $query->execute();

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    public static function getByName($name)
    {
        $query = Database::prepare("SELECT * FROM `locations` WHERE `name` = :name");

        $query->bindValue(":name", $name);

        $query->execute();

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `locations`
                SET
                    `name` = :name,
                    `latitude` = :latitude,
                    `longitude` = :longitude
            ");
        } else {
            $query = Database::prepare("
                UPDATE `locations`
                SET
                    `name` = :name,
                    `latitude` = :latitude,
                    `longitude` = :longitude
                WHERE `id` = :id
            ");

            $query->bindValue(":id", $this->id, PDO::PARAM_INT);
        }

        $query->bindValue(":name", $this->name);
        $query->bindValue(":latitude", $this->latitude);
        $query->bindValue(":longitude", $this->longitude);

        $query->execute();

        if ($this->id === null) {
            $this->id = (int)Database::lastInsertId();
        }
    }
}