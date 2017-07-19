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
        self::$pdo = new PDO(Config::getRequiredValue("database", "dsn"), Config::getValue("database", "username"), Config::getValue("database", "password"));

        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_CLASS);
        self::$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
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

    public static function beginTransaction()
    {
        return self::$pdo->beginTransaction();
    }

    public static function commit()
    {
        return self::$pdo->commit();
    }

    public static function rollBack()
    {
        return self::$pdo->rollBack();
    }
}