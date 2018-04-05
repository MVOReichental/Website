<?php
namespace de\mvo\model\messages;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\User;
use PDO;

class Messages extends ArrayObject
{
    public static function getAll($limit = 1000)
    {
        $query = Database::prepare("
            SELECT *
            FROM `messages`
            ORDER BY `id` DESC
            LIMIT :limit
        ");

        $query->bindValue(":limit", $limit, PDO::PARAM_INT);

        $query->execute();

        $messages = new self;

        while ($message = $query->fetchObject(Message::class)) {
            $messages->append($message);
        }

        return $messages;
    }

    public static function getBySender(User $user, $limit = 1000)
    {
        $query = Database::prepare("
            SELECT *
            FROM `messages`
            WHERE `senderUserId` = :senderUserId AND `visibleToSender`
            ORDER BY `id` DESC
            LIMIT :limit
        ");

        $query->bindValue(":senderUserId", $user->id, PDO::PARAM_INT);
        $query->bindValue(":limit", $limit, PDO::PARAM_INT);

        $query->execute();

        $messages = new self;

        while ($message = $query->fetchObject(Message::class)) {
            $messages->append($message);
        }

        return $messages;
    }

    public static function getByRecipient(User $user, $limit = 1000)
    {
        $query = Database::prepare("
            SELECT `messages`.*
            FROM `messagerecipients`
            LEFT JOIN `messages` ON `messages`.`id` = `messagerecipients`.`messageId`
            WHERE `messagerecipients`.`userId` = :recipientUserId AND `messagerecipients`.`visible`
            ORDER BY `messages`.`id` DESC
            LIMIT :limit
        ");

        $query->bindValue(":recipientUserId", $user->id, PDO::PARAM_INT);
        $query->bindValue(":limit", $limit, PDO::PARAM_INT);

        $query->execute();

        $messages = new self;

        while ($message = $query->fetchObject(Message::class)) {
            $messages->append($message);
        }

        return $messages;
    }

    public function hasMessage(Message $message)
    {
        /**
         * @var $thisMessage Message
         */
        foreach ($this as $thisMessage) {
            if ($thisMessage->id == $message->id) {
                return true;
            }
        }

        return false;
    }

    public function addAll(Messages $messages)
    {
        /**
         * @var $message Message
         */
        foreach ($messages as $message) {
            if ($this->hasMessage($message)) {
                continue;
            }

            $this->append($message);
        }
    }

    public function makeUnique()
    {
        $this->exchangeArray(array_unique((array)$this));

        return $this;
    }

    /**
     * @param mixed $index
     *
     * @return Message|null
     */
    public function offsetGet($index)
    {
        if (!$this->offsetExists($index)) {
            return null;
        }

        return parent::offsetGet($index);
    }
}