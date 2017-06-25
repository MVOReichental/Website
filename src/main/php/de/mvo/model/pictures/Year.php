<?php
namespace de\mvo\model\pictures;

class Year
{
    /**
     * @var int
     */
    public $year;
    /**
     * @var Album
     */
    public $coverAlbum;
    /**
     * @var AlbumList|null
     */
    public $albums;

    public function __construct($year)
    {
        $this->year = (int)$year;
        $this->albums = AlbumList::getForYear($this->year);

        /**
         * @var $album Album
         */
        foreach ($this->albums as $album) {
            if ($album->useAsYearCover) {
                $this->coverAlbum = $album;
                break;
            }
        }
    }

    public function __toString()
    {
        return (string)$this->year;
    }
}