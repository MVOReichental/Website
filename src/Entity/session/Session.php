<?php
namespace App\Entity\session;

use App\Database;
use App\Date;
use App\Entity\users\User;

class Session
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var Date
     */
    public $date;
    /**
     * @var string
     */
    public $data;
    /**
     * @var User|null
     */
    public $user;
    /**
     * @var int|null
     */
    private $userId;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->date = new Date($this->date);
        $this->user = $this->userId === null ? null : User::getById($this->userId);
    }

    /**
     * @param string $id
     * @return Session|null
     */
    public static function getById(string $id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `sessions`
            WHERE `id` = :id
        ");

        $query->bindValue(":id", $id);

        $query->execute();

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    /**
     * @return bool
     */
    public function hasUser()
    {
        return $this->user !== null;
    }

    public function save()
    {
        $query = Database::prepare("
            REPLACE INTO `sessions`
            SET
                `id` = :id,
                `date` = :date,
                `data` = :data,
                `userId` = :userId
        ");

        $query->bindValue(":id", $this->id);
        $query->bindValue(":date", $this->date->toDatabase());
        $query->bindValue(":data", $this->data);
        $query->bindValue(":userId", $this->user === null ? null : $this->user->id);

        $query->execute();
    }

    public function delete()
    {
        $query = Database::prepare("
            DELETE FROM `sessions`
            WHERE `id` = :id
        ");

        $query->bindValue(":id", $this->id);

        $query->execute();
    }
}