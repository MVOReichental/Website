<?php
namespace de\mvo\model\notedirectory;

use de\mvo\Database;

class Category
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var int
     */
    public $order;
    /**
     * @var Titles
     */
    public $titles;

    /**
     * @param int $id
     *
     * @return Category|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `notedirectorycategories`
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

    public function isEqualTo(Category $category)
    {
        return ($this->id == $category->id);
    }
}