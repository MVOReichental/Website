<?php
namespace de\mvo\mail;

use de\mvo\Config;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Message extends Email
{
    public function __construct()
    {
        parent::__construct();

        $this->from(new Address(Config::getValue("mail", "from"), Config::getValue("mail", "fromName")));
    }

    public function setSubjectFromHtml()
    {
        $document = new DOMDocument;

        $document->loadHTML($this->getHtmlBody());

        $xpath = new DOMXPath($document);

        $titleElement = $xpath->query("//html/head/title")->item(0);
        if ($titleElement === null) {
            return false;
        }

        $this->subject($titleElement->nodeValue);

        return true;
    }
}