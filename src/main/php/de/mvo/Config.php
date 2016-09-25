<?php
namespace de\mvo;

use com\selfcoders\pini\Pini;

class Config extends Pini
{
    /**
     * @var Pini
     */
    private static $pini;

    public static function getInstance()
    {
        if (self::$pini !== null) {
            return self::$pini;
        }

        self::$pini = new self(RESOURCES_ROOT . "/config.ini");

        return self::$pini;
    }

    public function getValue($section, $property)
    {
        $section = $this->getSection($section);
        if ($section === null) {
            return null;// TODO: Throw exception?
        }

        return $section->getPropertyValue($property);// TODO: Throw exception if null?
    }
}