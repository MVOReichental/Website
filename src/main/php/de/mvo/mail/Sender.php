<?php
namespace de\mvo\mail;

use de\mvo\Config;
use Swift_Mailer;
use Swift_SmtpTransport;

class Sender
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    public function __construct()
    {
        $smtpTransport = Swift_SmtpTransport::newInstance(Config::getValue("mail", "host", "localhost"), Config::getValue("mail", "port", 25), Config::getValue("mail", "encryption"));

        $smtpTransport->setUsername(Config::getValue("mail", "username"));
        $smtpTransport->setPassword(Config::getValue("mail", "password"));

        $this->mailer = Swift_Mailer::newInstance($smtpTransport);
    }

    public function send(Message $message)
    {
        $failedRecipients = array();

        $sent = $this->mailer->send($message, $failedRecipients);

        return ($sent > 0 and empty($failedRecipients));
    }
}