<?php
namespace de\mvo\model\protocols;

use ArrayObject;
use de\mvo\Database;

class Groups extends ArrayObject
{
    public static function getForProtocol(Protocol $protocol)
    {
        $query = Database::prepare("
            SELECT `name`
            FROM `protocolgroups`
            WHERE `protocolId` = :protocolId
        ");

        $query->execute(array
        (
            ":protocolId" => $protocol->id
        ));

        $list = new self;

        while ($group = $query->fetchColumn(0)) {
            $list->append($group);
        }

        return $list;
    }
}