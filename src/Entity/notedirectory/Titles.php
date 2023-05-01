<?php
namespace App\Entity\notedirectory;

use ArrayObject;
use App\Database;

class Titles extends ArrayObject
{
    public static function getAll()
    {
        $query = Database::query("
            SELECT *
            FROM `notedirectorytitles`
            ORDER BY `title` ASC
        ");

        $titles = new self;

        while ($title = $query->fetchObject(Title::class)) {
            $titles->append($title);
        }

        return $titles;
    }

    public static function search(string $keyword)
    {
        $query = Database::prepare("
            SELECT *
            FROM `notedirectorytitles`
            WHERE
              `title` LIKE :keyword OR
              `composer` LIKE :keyword OR
              `arranger` LIKE :keyword OR
              `publisher` LIKE :keyword
            ORDER BY `title` ASC
        ");

        $keyword = str_replace(array("\\", "_", "%"), array("\\\\", "\\_", "\\%"), $keyword);

        $query->bindValue(":keyword", "%" . $keyword . "%");

        $query->execute();

        $titles = new self;

        while ($title = $query->fetchObject(Title::class)) {
            $titles->append($title);
        }

        return $titles;
    }

    public function getById($id)
    {
        /**
         * @var $title Title
         */
        foreach ($this as $title) {
            if ($title->id == $id) {
                return $title;
            }
        }

        return null;
    }

    public function first()
    {
        return $this->offsetGet(0);
    }
}