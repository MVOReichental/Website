<?php
namespace App\Entity\messages;

use PDO;
use App\Database;
use App\Date;
use App\mail\Message as MailMessage;
use App\mail\Queue;
use App\Entity\uploads\Upload;
use App\Entity\uploads\Uploads;
use App\Entity\users\User;
use App\Entity\users\Users;
use App\TwigRenderer;
use App\utils\StringUtil;
use App\utils\Url;
use Symfony\Component\Mime\Address;
use Twig\Error\Error;

class Message
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var Date
     */
    public $date;
    /**
     * @var User
     */
    public $sender;
    /**
     * @var Users
     */
    public $recipients;
    /**
     * @var string
     */
    public $text;
    /**
     * @var bool
     */
    public $visibleToSender;
    /**
     * @var Uploads
     */
    public $attachments;

    private $senderUserId;

    public function __construct()
    {
        if ($this->id === null) {
            $this->visibleToSender = true;
            return;
        }

        $this->id = (int)$this->id;
        $this->visibleToSender = (bool)$this->visibleToSender;
        $this->date = new Date($this->date);
        $this->sender = User::getById($this->senderUserId);

        $query = Database::prepare("
            SELECT `users`.*
            FROM `messagerecipients`
            LEFT JOIN `users` ON `users`.`id` = `messagerecipients`.`userId`
            WHERE `messageId` = :messageId
        ");

        $query->bindValue(":messageId", $this->id, PDO::PARAM_INT);

        $query->execute();

        $this->recipients = new Users;

        while ($user = $query->fetchObject(User::class)) {
            $this->recipients->append($user);
        }

        $query = Database::prepare("
            SELECT `uploads`.*
            FROM `messageattachments`
            LEFT JOIN `uploads` ON `uploads`.`id` = `messageattachments`.`uploadId`
            WHERE `messageId` = :messageId
        ");

        $query->bindValue(":messageId", $this->id, PDO::PARAM_INT);

        $query->execute();

        $this->attachments = new Uploads;

        while ($upload = $query->fetchObject(Upload::class)) {
            $this->attachments->append($upload);
        }
    }

    /**
     * @param int $id
     *
     * @return Message|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `messages`
            WHERE `id` = :id
        ");

        $query->bindValue(":id", $id, PDO::PARAM_INT);

        $query->execute();

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    public function formatText()
    {
        return StringUtil::format($this->text);
    }

    public function isUserSenderOrRecipient(User $user)
    {
        if ($this->sender->isEqualTo($user)) {
            return true;
        }

        if ($this->recipients->hasUser($user)) {
            return true;
        }

        return false;
    }

    public function saveAsNew()
    {
        $query = Database::prepare("
            INSERT INTO `messages`
            SET
                `date` = NOW(),
                `senderUserId` = :senderUserId,
                `text` = :text
        ");

        $query->bindValue(":senderUserId", $this->sender->id, PDO::PARAM_INT);
        $query->bindValue(":text", $this->text);

        $query->execute();

        $this->id = (int)Database::lastInsertId();

        $query = Database::prepare("
            INSERT INTO `messagerecipients`
            SET
                `messageId` = :messageId,
                `userId` = :userId
        ");

        /**
         * @var $recipient User
         */
        foreach ($this->recipients as $recipient) {
            $query->bindValue(":messageId", $this->id, PDO::PARAM_INT);
            $query->bindValue(":userId", $recipient->id, PDO::PARAM_INT);

            $query->execute();
        }

        $query = Database::prepare("
            INSERT INTO `messageattachments`
            SET
                `messageId` = :messageId,
                `uploadId` = :uploadId
        ");

        /**
         * @var $attachment Upload
         */
        foreach ($this->attachments as $attachment) {
            $query->bindValue(":messageId", $this->id, PDO::PARAM_INT);
            $query->bindValue(":uploadId", $attachment->id, PDO::PARAM_INT);

            $query->execute();
        }
    }

    /**
     * @param bool $state
     */
    public function setVisibleToSender($state)
    {
        $query = Database::prepare("
            UPDATE `messages`
            SET `visibleToSender` = :visibleToSender
            WHERE `id` = :id
        ");

        $query->bindValue(":visibleToSender", $state, PDO::PARAM_BOOL);
        $query->bindValue(":id", $this->id, PDO::PARAM_INT);

        $query->execute();

        $this->visibleToSender = $state;
    }

    public function setVisibleToRecipient(User $user, $state)
    {
        $query = Database::prepare("
            UPDATE `messagerecipients`
            SET `visible` = :visible
            WHERE `messageId` = :messageId AND `userId` = :userId
        ");

        $query->bindValue(":visible", $state, PDO::PARAM_BOOL);
        $query->bindValue(":messageId", $this->id, PDO::PARAM_INT);
        $query->bindValue(":userId", $user->id, PDO::PARAM_INT);

        $query->execute();
    }

    /**
     * @throws Error
     */
    public function sendMail()
    {
        /**
         * @var $user User
         */
        foreach ($this->recipients as $user) {
            if ($user->email === null or $user->email === "") {
                continue;
            }

            $message = new MailMessage;

            $message->to(new Address($user->email, $user->getFullName()));
            $message->replyTo(new Address($this->sender->email, $this->sender->getFullName()));
            $message->html(TwigRenderer::render("messages/mail", array
            (
                "sender" => $this->sender,
                "message" => $this->formatText(),
                "attachments" => $this->attachments,
                "baseUrl" => Url::getBaseUrl(),
                "url" => sprintf("%s/internal/messages/%d", Url::getBaseUrl(), $this->id)
            )), "text/html");
            $message->setSubjectFromHtml();

            Queue::add($message);
        }
    }
}