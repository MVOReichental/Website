<?php
namespace de\mvo\service;

use de\mvo\model\messages\Message;
use de\mvo\model\messages\Messages as MessagesList;
use de\mvo\model\uploads\Upload;
use de\mvo\model\uploads\Uploads;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use de\mvo\uploadhandler\File;
use de\mvo\uploadhandler\Files;
use Twig\Error\Error;

class Messages extends AbstractService
{
    /**
     * @return string
     * @throws Error
     */
    public function getSentMessages()
    {
        return TwigRenderer::render("messages/page", array
        (
            "title" => "Gesendete Nachrichten",
            "messages" => MessagesList::getBySender(User::getCurrent())
        ));
    }

    /**
     * @return string
     * @throws Error
     */
    public function getReceivedMessages()
    {
        return TwigRenderer::render("messages/page", array
        (
            "title" => "Empfangene Nachrichten",
            "messages" => MessagesList::getByRecipient(User::getCurrent())
        ));
    }

    /**
     * @return string
     * @throws Error
     */
    public function getAllMessages()
    {
        return TwigRenderer::render("messages/page", array
        (
            "title" => "Alle Nachrichten",
            "messages" => MessagesList::getAll()
        ));
    }

    /**
     * @return null|string
     * @throws Error
     */
    public function sendMessage()
    {
        if (!isset($_POST["text"]) or !isset($_POST["recipients"])) {
            http_response_code(400);
            return null;
        }

        $recipients = json_decode($_POST["recipients"]);
        if ($recipients === null or !is_array($recipients)) {
            http_response_code(400);
            return null;
        }

        $files = new Files($_FILES["files"]);

        /**
         * @var $file File
         */
        foreach ($files as $file) {
            switch ($file->error) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_INI_SIZE:
                    return TwigRenderer::render("messages/send-error", array
                    (
                        "message" => "Die maximale Dateigr&ouml;&szlig;e wurde erreicht!"
                    ));
                default:
                    return TwigRenderer::render("messages/send-error", array
                    (
                        "message" => "Beim Hochladen ist ein Fehler aufgetreten!"
                    ));
            }
        }

        $message = new Message;

        $message->sender = User::getCurrent();
        $message->text = $_POST["text"];

        $message->attachments = new Uploads;

        /**
         * @var $file File
         */
        foreach ($files as $file) {
            $upload = Upload::add($file->tempName, $file->name);
            if ($upload === null) {
                return TwigRenderer::render("messages/send-error", array
                (
                    "message" => "Beim Hochladen ist ein Fehler aufgetreten!"
                ));
            }

            $message->attachments->append($upload);
        }

        $message->recipients = new Users;

        foreach ($recipients as $userId) {
            $user = User::getById($userId);
            if ($user === null) {
                continue;
            }

            $message->recipients->append($user);
        }

        $message->saveAsNew();
        $message->sendMail();

        header(sprintf("Location: /internal/messages/%d?sent", $message->id));
        return null;
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws Error
     */
    public function showMessage()
    {
        $message = Message::getById($this->params->id);

        if ($message === null) {
            throw new NotFoundException;
        }

        if (!$message->isUserSenderOrRecipient(User::getCurrent())) {
            throw new NotFoundException;
        }

        return TwigRenderer::render("messages/single-message-page", array
        (
            "showSentInfo" => isset($_GET["sent"]),
            "message" => $message
        ));
    }

    /**
     * @throws NotFoundException
     */
    public function hideMessageForUser()
    {
        $currentUser = User::getCurrent();

        $message = Message::getById($this->params->id);
        if ($message === null) {
            throw new NotFoundException;
        }

        if ($message->sender->isEqualTo($currentUser)) {
            $message->setVisibleToSender(false);
        }

        if ($message->recipients->hasUser($currentUser)) {
            $message->setVisibleToRecipient($currentUser, false);
        }
    }
}