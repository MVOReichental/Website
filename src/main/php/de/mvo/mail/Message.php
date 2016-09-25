<?php
namespace de\mvo\mail;

use de\mvo\Config;
use DOMDocument;
use DOMXPath;
use Swift_Message;

class Message extends Swift_Message
{
    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        parent::__construct($subject, $body, $contentType, $charset);

        $config = Config::getInstance()->getSection("mail");

        $this->setFrom($config->getPropertyValue("from"), $config->getPropertyValue("fromName"));
    }

    public function setSubjectFromHtml($html)
    {
        $document = new DOMDocument;

        $document->loadHTML($html);

        $xpath = new DOMXPath($document);

        $titleElement = $xpath->query("//html/head/title")->item(0);
        if ($titleElement === null) {
            return false;
        }

        $this->setSubject($titleElement->nodeValue);

        return true;
    }
}