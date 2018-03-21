<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use de\mvo\model\users\User;
use DirectoryIterator;

class AlbumList extends ArrayObject
{
    public static function getForYear($year)
    {
        $year = (int)$year;

        if (!is_dir(PICTURES_ROOT . "/" . $year)) {
            return null;
        }

        $list = new self;

        foreach (new DirectoryIterator(PICTURES_ROOT . "/" . $year) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if (!$item->isDir()) {
                continue;
            }

            $file = $item->getPath() . "/" . $item->getFilename() . "/album.json";
            if (!file_exists($file)) {
                continue;
            }

            $list->append(new Album($year, $item->getFilename()));
        }

        return $list;
    }

    public static function getAll()
    {
        $albums = new self;

        /**
         * @var $year Year
         */
        foreach (new YearList as $year) {
            $yearAlbums = $year->albums;
            if ($yearAlbums === null) {
                continue;
            }

            /**
             * @var $album Album
             */
            foreach ($yearAlbums as $album) {
                $albums->append($album);
            }
        }

        return $albums;
    }

    public function hasAlbumsVisibleToUser(User $user = null)
    {
        /**
         * @var $album Album
         */
        foreach ($this as $album) {
            if ($album->isVisibleToUser($user)) {
                return true;
            }
        }

        return false;
    }

    public function getVisibleToUser(User $user = null)
    {
        $albums = new self;

        /**
         * @var $album Album
         */
        foreach ($this as $album) {
            if (!$album->isVisibleToUser($user)) {
                continue;
            }

            $albums->append($album);
        }

        return $albums;
    }

    /**
     * @param string $name
     *
     * @return Album|null
     */
    public function getAlbum($name)
    {
        /**
         * @var $album Album
         */
        foreach ($this as $album) {
            if ($album->name == $name) {
                return $album;
            }
        }

        return null;
    }

    public function sortByDate($ascending = true)
    {
        $this->uasort(function (Album $album1, Album $album2) use ($ascending) {
            if ($album1->date > $album2->date) {
                return $ascending ? 1 : -1;
            }

            if ($album1->date < $album2->date) {
                return $ascending ? -1 : 1;
            }

            return 0;
        });

        return $this;
    }

    public function slice($offset, $length)
    {
        return new self(array_slice($this->getArrayCopy(), $offset, $length));
    }
}