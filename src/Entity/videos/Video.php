<?php
namespace App\Entity\videos;

use App\Date;

class Video
{
    /**
     * @var string
     */
    public $videoId;
    /**
     * @var Date
     */
    public $date;
    /**
     * @var string
     */
    public $title;

    public function getUrl()
    {
        return sprintf("https://www.youtube.com/watch?v=%s", $this->videoId);
    }

    public function getEmbedUrl()
    {
        return sprintf("https://www.youtube.com/embed/%s?rel=0", $this->videoId);
    }

    public function getThumbnailUrl()
    {
        return sprintf("https://img.youtube.com/vi/%s/0.jpg", $this->videoId);
    }
}