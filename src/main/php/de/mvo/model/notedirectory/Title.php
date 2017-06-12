<?php
namespace de\mvo\model\notedirectory;

use de\mvo\Database;
use PDO;

class Title
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $number;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $composer;
    /**
     * @var string
     */
    public $arranger;
    /**
     * @var string
     */
    public $publisher;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->number = (int)$this->number;
    }

    /**
     * @param int $id
     *
     * @return Title|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `notedirectorytitles`
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

    public function save($forceInsertWithId = false)
    {
        if ($this->id === null or $forceInsertWithId) {
            if ($forceInsertWithId) {
                $query = Database::prepare("
                    INSERT INTO `notedirectorytitles`
                    SET
                        `id` = :id,
                        `title` = :title,
                        `composer` = :composer,
                        `arranger` = :arranger,
                        `publisher` = :publisher
                ");

                $query->bindValue(":id", $this->id, PDO::PARAM_INT);
            } else {
                $query = Database::prepare("
                    INSERT INTO `notedirectorytitles`
                    SET
                        `title` = :title,
                        `composer` = :composer,
                        `arranger` = :arranger,
                        `publisher` = :publisher
                ");
            }
        } else {
            $query = Database::prepare("
                UPDATE `notedirectorytitles`
                SET
                    `title` = :title,
                    `composer` = :composer,
                    `arranger` = :arranger,
                    `publisher` = :publisher
                WHERE `id` = :id
            ");

            $query->bindValue(":id", $this->id, PDO::PARAM_INT);
        }

        $query->bindValue(":title", $this->title);
        $query->bindValue(":composer", $this->composer);
        $query->bindValue(":arranger", $this->arranger);
        $query->bindValue(":publisher", $this->publisher);

        $query->execute();

        if ($this->id === null) {
            $this->id = (int)Database::lastInsertId();
        }
    }

    public function delete()
    {
        $query = Database::prepare("
            DELETE FROM `notedirectorytitles`
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":id" => $this->id
        ));
    }

    public function programs()
    {
        return Programs::getProgramsContainingTitle($this);
    }
}