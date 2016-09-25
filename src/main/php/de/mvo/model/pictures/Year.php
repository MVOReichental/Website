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

        $file = PICTURES_ROOT . "/" . $this->year . "/year.json";
        if (!file_exists($file)) {
            return null;
        }

        $yearData = json_decode(file_get_contents($file));

        $this->coverAlbum = new Album($this->year, $yearData->coverAlbum);
        $this->albums = AlbumList::getForYear($this->year);
    }

    public function __toString()
    {
        return (string)$this->year;
    }
}