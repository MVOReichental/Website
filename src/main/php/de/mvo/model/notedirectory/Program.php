<?php
namespace de\mvo\model\notedirectory;

use de\mvo\Database;
use de\mvo\utils\StringUtil;
use PDO;

class Program
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $year;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $title;
    /**
     * @var Titles
     */
    public $titles;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->year = (int)$this->year;

        $query = Database::prepare("
            SELECT `notedirectorytitles`.*, `number`
            FROM `notedirectoryprogramtitles`
            LEFT JOIN `notedirectorytitles` ON `notedirectorytitles`.`id` = `notedirectoryprogramtitles`.`titleId`
            WHERE `programId` = :programId
            ORDER BY `number`
        ");

        $query->execute(array
        (
            ":programId" => $this->id
        ));

        $this->titles = new Titles;

        while ($title = $query->fetchObject(Title::class)) {
            $this->titles->append($title);
        }
    }

    /**
     * @return Program|null
     */
    public static function getLatest()
    {
        $query = Database::query("
            SELECT *
            FROM `notedirectoryprograms`
            WHERE `name` = 'jahresprogramm'
            ORDER BY `year` DESC
            LIMIT 1
        ");

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    /**
     * @param int $year
     * @param string $name
     *
     * @return Program|null
     */
    public static function getByYearAndName($year, $name)
    {
        $query = Database::prepare("
            SELECT *
            FROM `notedirectoryprograms`
            WHERE `year` = :year AND `name` = :name
        ");

        $query->execute(array
        (
            ":year" => $year,
            ":name" => $name
        ));

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    /**
     * @param int $id
     *
     * @return Program|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `notedirectoryprograms`
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

    public function generateName()
    {
        $this->name = strtolower(StringUtil::removeNonAlphanumeric($this->title));
    }

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `notedirectoryprograms`
                SET
                    `year` = :year,
                    `name` = :name,
                    `title` = :title
            ");
        } else {
            $query = Database::prepare("
                UPDATE `notedirectoryprograms`
                SET
                    `year` = :year,
                    `name` = :name,
                    `title` = :title
                WHERE `id` = :id
            ");

            $query->bindValue(":id", $this->id, PDO::PARAM_INT);
        }

        $query->bindValue(":year", $this->year);
        $query->bindValue(":name", $this->name);
        $query->bindValue(":title", $this->title);

        $query->execute();

        if ($this->id === null) {
            $this->id = (int)Database::lastInsertId();
        }

        Database::beginTransaction();

        $deleteQuery = Database::prepare("
            DELETE FROM `notedirectoryprogramtitles`
            WHERE `programId` = :programId AND `titleId` = :titleId
        ");

        $query = Database::prepare("
            SELECT `titleId`
            FROM `notedirectoryprogramtitles`
            WHERE `programId` = :programId
        ");

        $query->execute(array
        (
            ":programId" => $this->id
        ));

        while ($row = $query->fetch(PDO::FETCH_OBJ)) {
            $found = false;
            /**
             * @var $title Title
             */
            foreach ($this->titles as $title) {
                if ($title->id == $row->titleId) {
                    $found = true;
                    continue;
                }
            }

            if (!$found) {
                $deleteQuery->execute(array
                (
                    ":programId" => $this->id,
                    ":titleId" => $row->titleId
                ));
            }
        }

        $selectQuery = Database::prepare("
            SELECT `number`
            FROM `notedirectoryprogramtitles`
            WHERE `programId` = :programId AND `titleId` = :titleId
        ");

        $updateQuery = Database::prepare("
            UPDATE `notedirectoryprogramtitles`
            SET `number` = :number
            WHERE `programId` = :programId AND `titleId` = :titleId
        ");

        $insertQuery = Database::prepare("
            INSERT INTO `notedirectoryprogramtitles`
            SET
                `programId` = :programId,
                `titleId` = :titleId,
                `number` = :number
        ");

        /**
         * @var $title Title
         */
        foreach ($this->titles as $title) {
            $selectQuery->execute(array
            (
                ":programId" => $this->id,
                ":titleId" => $title->id
            ));

            if ($selectQuery->rowCount()) {
                if ($selectQuery->fetch(PDO::FETCH_OBJ)->number == $title->number) {
                    continue;
                }

                $updateQuery->execute(array
                (
                    ":programId" => $this->id,
                    ":titleId" => $title->id,
                    ":number" => $title->number
                ));
            } else {
                $insertQuery->execute(array
                (
                    ":programId" => $this->id,
                    ":titleId" => $title->id,
                    ":number" => $title->number
                ));
            }
        }

        Database::commit();
    }

    public function delete()
    {
        $query = Database::prepare("
            DELETE FROM `notedirectoryprograms`
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":id" => $this->id
        ));
    }
}