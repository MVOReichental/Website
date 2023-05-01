<?php
namespace App\mail;

use App\Config;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class Sender
{
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct()
    {
        $smtpTransport = new EsmtpTransport(Config::getValue("mail", "host", "localhost"), Config::getValue("mail", "port", 25), Config::getValue("mail", "encryption"));

        $smtpTransport->setUsername(Config::getValue("mail", "username"));
        $smtpTransport->setPassword(Config::getValue("mail", "password"));

        $this->mailer = new Mailer($smtpTransport);
    }

    public function send(Message $message)
    {
        $this->mailer->send($message);
    }
}