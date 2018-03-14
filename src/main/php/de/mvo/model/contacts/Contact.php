<?php
namespace de\mvo\model\contacts;

use de\mvo\Database;
use de\mvo\model\users\User;
use PDO;

class Contact
{
    const TYPE_TILES = array
    (
        "mobile" => "Mobil",
        "phone" => "Telefon"
    );

    const CATEGORY_TITLES = array
    (
        "business" => "Gesch&auml;ftlich",
        "private" => "Privat"
    );

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $category;
    /**
     * @var string
     */
    public $value;
    /**
     * @var User
     */
    public $user;
    /**
     * @var int
     */
    private $userId;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;

        if ($this->userId !== null) {
            $this->userId = (int)$this->userId;
            $this->user = User::getById($this->userId);
        }
    }

    /**
     * @param int $id
     *
     * @return Contact|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `usercontacts`
            WHERE `id` = :id
        ");

        $query->execute(array
        (
            ":id" => $id
        ));

        return $query->fetchObject(Contact::class);
    }

    public function getTitle()
    {
        return self::TYPE_TILES[$this->type] . " (" . self::CATEGORY_TITLES[$this->category] . ")";
    }

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `usercontacts`
                SET
                    `userId` = :userId,
                    `type` = :type,
                    `category` = :category,
                    `value`= :value
            ");
        } else {
            $query = Database::prepare("
                UPDATE `usercontacts`
                SET
                    `userId` = :userId,
                    `type` = :type,
                    `category` = :category,
                    `value` = :value
                WHERE `id` = :id
            ");

            $query->bindValue(":id", $this->id, PDO::PARAM_INT);
        }

        $query->bindValue(":userId", $this->user->id, PDO::PARAM_INT);
        $query->bindValue(":type", $this->type);
        $query->bindValue(":category", $this->category);
        $query->bindValue(":value", $this->value);

        $query->execute();

        if ($this->id === null) {
            $this->id = (int)Database::lastInsertId();
        }
    }

    public function remove()
    {
        if ($this->id === null) {
            return;
        }

        $query = Database::prepare("DELETE FROM `usercontacts` WHERE `id` = :id");

        $query->bindValue(":id", $this->id, PDO::PARAM_INT);

        $query->execute();
    }
}