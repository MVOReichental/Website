<?php
namespace de\mvo\model\messages;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\mail\Message as MailMessage;
use de\mvo\mail\Queue;
use de\mvo\model\uploads\Upload;
use de\mvo\model\uploads\Uploads;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\TwigRenderer;
use de\mvo\utils\StringUtil;
use de\mvo\utils\Url;
use Twig_Error;

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

        $query->execute(array
        (
            ":messageId" => $this->id
        ));

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

        $query->execute(array
        (
            ":messageId" => $this->id
        ));

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

        $query->execute(array
        (
            ":id" => $id
        ));

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

        $query->execute(array
        (
            ":senderUserId" => $this->sender->id,
            ":text" => $this->text
        ));

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
            $query->execute(array
            (
                ":messageId" => $this->id,
                ":userId" => $recipient->id
            ));
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
            $query->execute(array
            (
                ":messageId" => $this->id,
                ":uploadId" => $attachment->id
            ));
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

        $query->execute(array
        (
            ":visibleToSender" => (int)$state,
            ":id" => $this->id
        ));

        $this->visibleToSender = $state;
    }

    public function setVisibleToRecipient(User $user, $state)
    {
        $query = Database::prepare("
            UPDATE `messagerecipients`
            SET `visible` = :visible
            WHERE `messageId` = :messageId AND `userId` = :userId
        ");

        $query->execute(array
        (
            ":visible" => (int)$state,
            ":messageId" => $this->id,
            ":userId" => $user->id
        ));
    }

    /**
     * @throws Twig_Error
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

            $message->setTo($user->email, $user->getFullName());
            $message->setReplyTo($this->sender->email, $this->sender->getFullName());
            $message->setBody(TwigRenderer::render("messages/mail", array
            (
                "sender" => $this->sender,
                "message" => $this->formatText(),
                "attachments" => $this->attachments,
                "baseUrl" => Url::getBaseUrl(),
                "url" => sprintf("%s/internal/messages/%d", Url::getBaseUrl(), $this->id)
            )), "text/html");
            $message->setSubjectFromHtml($message->getBody());

            Queue::add($message);
        }
    }
}