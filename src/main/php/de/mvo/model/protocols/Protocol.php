<?php
namespace de\mvo\model\protocols;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\mail\Message;
use de\mvo\mail\Queue;
use de\mvo\model\uploads\Upload;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\TwigRenderer;
use de\mvo\utils\Url;
use PDO;
use Symfony\Component\Mime\Address;
use Twig\Error\Error;

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
     * @var User
     */
    public $uploader;
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
    private $uploaderUserId;
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
        $this->uploader = User::getById($this->uploaderUserId);
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
                `uploaderUserId` = :uploaderUserId,
                `uploadId` = :uploadId,
                `title` = :title,
                `date` = :date
        ");

        $query->bindValue(":uploaderUserId", $this->uploader->id, PDO::PARAM_INT);
        $query->bindValue(":uploadId", $this->upload->id, PDO::PARAM_INT);
        $query->bindValue(":title", $this->title);
        $query->bindValue(":date", $this->date->toDatabaseDate());

        $query->execute();

        $this->id = (int)Database::lastInsertId();

        $query = Database::prepare("
            INSERT INTO `protocolgroups`
            SET
                `protocolId` = :protocolId,
                `name` = :name
        ");

        foreach ($this->groups as $group) {
            $query->bindValue(":protocolId", $this->id, PDO::PARAM_INT);
            $query->bindValue(":name", $group);

            $query->execute();
        }
    }

    /**
     * @throws Error
     */
    public function sendMail()
    {
        /**
         * @var $user User
         */
        foreach (Users::getAll() as $user) {
            if ($user->email === null or $user->email === "") {
                continue;
            }

            foreach ($this->groups as $group) {
                if ($user->hasPermission(sprintf("protocols.view.%s", $group))) {
                    $message = new Message;

                    $message->to(new Address($user->email, $user->getFullName()));
                    $message->replyTo(new Address($this->uploader->email, $this->uploader->getFullName()));
                    $message->html(TwigRenderer::render("protocols/mail", array
                    (
                        "uploader" => $this->uploader,
                        "date" => $this->date,
                        "title" => $this->title,
                        "url" => Url::getBaseUrl() . $this->upload->getUrl()
                    )), "text/html");
                    $message->setSubjectFromHtml($message->getBody());

                    Queue::add($message);
                    break;
                }
            }
        }
    }
}