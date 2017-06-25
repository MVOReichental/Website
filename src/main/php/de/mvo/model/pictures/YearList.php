<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use DirectoryIterator;
use UnexpectedValueException;

class YearList extends ArrayObject
{
    /**
     * @var YearList
     */
    private static $yearList;

    /**
     * @return YearList
     */
    public static function load()
    {
        if (self::$yearList !== null) {
            return self::$yearList;
        }

        $filename = RESOURCES_ROOT . "/pictures.serialized";

        if (file_exists($filename)) {
            self::$yearList = unserialize(file_get_contents($filename));
        } else {
            self::$yearList = new self;
        }

        if (self::$yearList instanceof self) {
            return self::$yearList;
        }

        throw new UnexpectedValueException("Unserialized data is not of type YearList");
    }

    public function save()
    {
        file_put_contents(RESOURCES_ROOT . "/pictures.serialized", serialize($this));
    }

    /**
     * @return YearList
     */
    public static function loadFromJson()
    {
        $list = new self;

        foreach (new DirectoryIterator(PICTURES_ROOT) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if (!$item->isDir()) {
                continue;
            }

            $list->append(new Year($item->getFilename()));
        }

        $list->asort();
        $list->exchangeArray(array_reverse($list->getArrayCopy()));

        return $list;
    }

    /**
     * @return AlbumList
     */
    public function getAllAlbums()
    {
        $albums = new AlbumList;

        /**
         * @var $year Year
         */
        foreach ($this as $year) {
            if ($year->albums === null) {
                continue;
            }

            /**
             * @var $album Album
             */
            foreach ($year->albums as $album) {
                $albums->append($album);
            }
        }

        return $albums;
    }

    /**
     * @param int $year
     *
     * @return Year|null
     */
    public function getYear($year)
    {
        /**
         * @var $yearInstance Year
         */
        foreach ($this as $yearInstance) {
            if ($yearInstance->year == $year) {
                return $yearInstance;
            }
        }

        return null;
    }
}