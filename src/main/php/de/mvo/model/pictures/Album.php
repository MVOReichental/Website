<?php
namespace de\mvo\model\pictures;

use de\mvo\Date;
use de\mvo\model\users\User;

class Album
{
    /**
     * @var int
     */
    public $year;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $text;
    /**
     * @var bool
     */
    public $isPublic;
    /**
     * @var bool
     */
    public $useAsYearCover;
    /**
     * @var Date
     */
    public $date;
    /**
     * @var Picture
     */
    public $cover;
    /**
     * @var Picture[]
     */
    public $pictures;

    public function __construct($year, $name)
    {
        $this->year = (int)$year;
        $this->name = basename($name);

        $file = PICTURES_ROOT . "/" . $this->year . "/" . $this->name . "/album.json";
        if (!file_exists($file)) {
            return null;
        }

        $albumData = json_decode(file_get_contents($file));

        $this->title = $albumData->title;
        $this->text = $albumData->text;
        $this->isPublic = $albumData->isPublic;
        $this->useAsYearCover = $albumData->useAsYearCover;
        $this->date = new Date($albumData->date);

        $this->pictures = array();

        foreach ($albumData->pictures as $pictureData) {
            $picture = new Picture;

            $picture->hash = $pictureData->hash;
            $picture->title = $pictureData->title;

            $this->pictures[$picture->hash] = $picture;
        }

        if (isset($this->pictures[$albumData->coverPicture])) {
            $this->cover = $this->pictures[$albumData->coverPicture];
        }
    }

    public function isVisibleToUser(User $user = null)
    {
        if ($this->isPublic) {
            return true;
        }

        return $user !== null;
    }
}