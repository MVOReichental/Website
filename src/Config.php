<?php
namespace App;

use com\selfcoders\pini\Pini;
use UnexpectedValueException;

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

        self::$pini = new self(DATA_ROOT . "/config.ini");

        return self::$pini;
    }

    public static function getValue($section, $property, $defaultValue = null)
    {
        $section = self::getInstance()->getSection($section);
        if ($section === null) {
            return $defaultValue;
        }

        return $section->getPropertyValue($property, $defaultValue);
    }

    public static function getRequiredValue($section, $property)
    {
        $value = self::getValue($section, $property);
        if ($value === null) {
            throw new UnexpectedValueException("Configuration property '" . $property . "' in section '" . $section . "' is required but undefined");
        }

        return $value;
    }
}