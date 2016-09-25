<?php
namespace de\mvo;

use PDO;

class Database
{
    /**
     * @var PDO
     */
    private static $pdo;

    public static function init()
    {
        $config = Config::getInstance();

        self::$pdo = new PDO($config->getValue("database", "dsn"), $config->getValue("database", "username"), $config->getValue("database", "password"));

        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_CLASS);
    }

    public static function prepare($statement)
    {
        return self::$pdo->prepare($statement);
    }

    public static function query($statement)
    {
        return self::$pdo->query($statement);
    }

    public static function pdo()
    {
        return self::$pdo;
    }

    public static function lastInsertId()
    {
        return self::$pdo->lastInsertId();
    }
}