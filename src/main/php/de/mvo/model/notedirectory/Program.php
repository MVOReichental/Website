<?php
namespace de\mvo\model\notedirectory;

use de\mvo\Database;

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
    /**
     * @var bool
     */
    public $isDefault;
    /**
     * @var bool
     */
    public $showCategories;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->year = (int)$this->year;
        $this->isDefault = (bool)$this->isDefault;
        $this->showCategories = (bool)$this->showCategories;

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
            WHERE `isDefault`
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
     * @param string $title
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

    public function categories()
    {
        return $this->titles->getInCategories();
    }
}