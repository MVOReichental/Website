<?php
namespace App\Entity\protocols;

use ArrayObject;
use App\Database;
use App\Entity\users\User;

class ProtocolsList extends ArrayObject
{
    public static function get()
    {
        $query = Database::query("
            SELECT *
            FROM `protocols`
            ORDER BY `date` DESC, `title` ASC
        ");

        $list = new self;

        while ($protocol = $query->fetchObject(Protocol::class)) {
            $list->append($protocol);
        }

        return $list;
    }

    public function getVisibleForUser(User $user)
    {
        $list = new self;

        /**
         * @var $protocol Protocol
         */
        foreach ($this as $protocol) {
            if (!$protocol->isVisibleForUser($user)) {
                continue;
            }

            $list->append($protocol);
        }

        return $list;
    }
}