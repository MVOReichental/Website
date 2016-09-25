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
        $config = Config::getInstance()->getSection("mail");

        $smtpTransport = Swift_SmtpTransport::newInstance($config->getPropertyValue("host", "localhost"), $config->getPropertyValue("port", 25), $config->getPropertyValue("encryption"));

        $smtpTransport->setUsername($config->getPropertyValue("username"));
        $smtpTransport->setPassword($config->getPropertyValue("password"));

        $this->mailer = Swift_Mailer::newInstance($smtpTransport);
    }

    public function send(Message $message)
    {
        $failedRecipients = array();

        $sent = $this->mailer->send($message, $failedRecipients);

        return ($sent > 0 and empty($failedRecipients));
    }
}