<?php
namespace de\mvo\model\protocols;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\uploads\Upload;
use de\mvo\model\users\User;

class Protocol
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
     * @var Date
     */
    public $date;
    /**
     * @var Upload|null
     */
    public $upload;
    /**
     * @var Groups
     */
    public $groups;
    /**
     * @var int
     */
    private $uploadId;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->date = new Date($this->date);
        $this->upload = Upload::getById($this->uploadId);
        $this->groups = Groups::getForProtocol($this);
    }

    public function isVisibleForUser(User $user)
    {
        foreach ($this->groups as $group) {
            if ($user->hasPermission("protocols.view." . $group)) {
                return true;
            }
        }

        return false;
    }

    public function save()
    {
        $query = Database::prepare("
            INSERT INTO `protocols`
            SET
                `uploadId` = :uploadId,
                `title` = :title,
                `date` = :date
        ");

        $query->execute(array
        (
            ":uploadId" => $this->upload->id,
            ":title" => $this->title,
            ":date" => $this->date
        ));

        $this->id = Database::lastInsertId();

        $query = Database::prepare("
            INSERT INTO `protocolgroups`
            SET
                `protocolId` = :protocolId,
                `name` = :name
        ");

        foreach ($this->groups as $group) {
            $query->execute(array
            (
                ":protocolId" => $this->id,
                ":name" => $group
            ));
        }
    }
}