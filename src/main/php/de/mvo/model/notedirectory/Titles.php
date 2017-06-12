<?php
namespace de\mvo\model\notedirectory;

use ArrayObject;
use de\mvo\Database;

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