<?php
namespace App\Entity\contacts;

use ArrayObject;
use PDO;
use App\Database;
use App\Entity\users\User;

class Contacts extends ArrayObject
{
    public static function forUser(User $user)
    {
        $contacts = new self;

        $query = Database::prepare("
            SELECT *
            FROM `usercontacts`
            WHERE `userId` = :userId
        ");

        $query->bindValue(":userId", $user->id, PDO::PARAM_INT);

        $query->execute();

        while ($contact = $query->fetchObject(Contact::class)) {
            $contacts->append($contact);
        }

        return $contacts;
    }
}