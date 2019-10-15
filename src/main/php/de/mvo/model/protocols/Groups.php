<?php
namespace de\mvo\model\protocols;

use ArrayObject;
use de\mvo\Database;
use PDO;

class Groups extends ArrayObject
{
    public static function getForProtocol(Protocol $protocol)
    {
        $query = Database::prepare("
            SELECT `name`
            FROM `protocolgroups`
            WHERE `protocolId` = :protocolId
        ");

        $query->bindValue(":protocolId", $protocol->id, PDO::PARAM_INT);

        $query->execute();

        $list = new self;

        while ($group = $query->fetchColumn(0)) {
            $list->append($group);
        }

        return $list;
    }
}