<?php
namespace de\mvo\model\notedirectory;

use de\mvo\Database;
use PDO;

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

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->order = (int)$this->order;
    }

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

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `notedirectorycategories`
                SET
                    `title` = :title,
                    `order` = :order
            ");
        } else {
            $query = Database::prepare("
                UPDATE `notedirectorycategories`
                SET
                    `title` = :title,
                    `order` = :order
                WHERE `id` = :id
            ");

            $query->bindValue(":id", $this->id, PDO::PARAM_INT);
        }

        $query->bindValue(":title", $this->title);
        $query->bindValue(":order", $this->order, PDO::PARAM_INT);

        $query->execute();

        $this->id = (int)Database::lastInsertId();
    }
}