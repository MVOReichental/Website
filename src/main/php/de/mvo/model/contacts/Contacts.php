<?php
namespace de\mvo\model\contacts;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\User;
use PDO;

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