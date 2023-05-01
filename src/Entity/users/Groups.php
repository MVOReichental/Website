<?php
namespace App\Entity\users;

class Groups
{
    /**
     * @var array
     */
    private static $json;

    private static function load()
    {
        if (self::$json !== null) {
            return;
        }

        self::$json = json_decode(file_get_contents(MODELS_ROOT . "/user-groups.json"), true);
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        self::load();

        $groups = array();

        foreach (self::$json as $name => $group) {
            $groups[$name] = $group["title"];
        }

        return $groups;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasGroup(string $name)
    {
        self::load();

        return isset(self::$json[$name]);
    }
}