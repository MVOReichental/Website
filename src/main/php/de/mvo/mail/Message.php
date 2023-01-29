<?php
namespace de\mvo\mail;

use de\mvo\Config;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Message extends Email
{
    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        parent::__construct();

        $this->from(new Address(Config::getValue("mail", "from"), Config::getValue("mail", "fromName")));
        $this->subject($subject);
        $this->html($body);
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

        $this->subject($titleElement->nodeValue);

        return true;
    }
}