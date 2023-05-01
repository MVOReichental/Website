<?php
namespace App\Entity\videos;

use ArrayObject;
use Google_Client;
use Google_Service_YouTube;
use App\Config;
use App\Date;
use UnexpectedValueException;

class VideoList extends ArrayObject
{
    /**
     * @return VideoList
     */
    public static function load()
    {
        if (!file_exists(DATA_ROOT . "/videos.serialized")) {
            return new VideoList;
        }

        $data = unserialize(file_get_contents(DATA_ROOT . "/videos.serialized"));

        if ($data instanceof self) {
            return $data;
        }

        throw new UnexpectedValueException("Unserialized data is not of type VideoList");
    }

    public function save()
    {
        file_put_contents(DATA_ROOT . "/videos.serialized", serialize($this));
    }

    /**
     * @return VideoList
     */
    public static function loadFromYouTubeAPI()
    {
        $client = new Google_Client;

        $client->setApplicationName("MVO Website");
        $client->setDeveloperKey(Config::getRequiredValue("google", "developer-key"));

        $service = new Google_Service_YouTube($client);

        $response = $service->playlistItems->listPlaylistItems("snippet", array
        (
            "playlistId" => Config::getRequiredValue("google", "youtube-playlistId"),
            "maxResults" => 50
        ));

        $videoIds = array();

        foreach ($response["items"] as $item) {
            $videoIds[] = $item["snippet"]["resourceId"]["videoId"];
        }

        $response = $service->videos->listVideos("snippet,recordingDetails", array
        (
            "id" => join(",", $videoIds),
            "maxResults" => 50
        ));

        $videos = new self;

        foreach ($response["items"] as $item) {
            $video = new Video;

            $video->title = $item["snippet"]["title"];
            $video->videoId = $item["id"];
            $video->date = new Date($item["recordingDetails"]["recordingDate"]);

            $videos->append($video);
        }

        return $videos;
    }

    public function sortByDate($ascending = true, $titleAscending = true)
    {
        $this->uasort(function (Video $video1, Video $video2) use ($ascending, $titleAscending) {
            if ($video1->date > $video2->date) {
                return $ascending ? 1 : -1;
            }

            if ($video1->date < $video2->date) {
                return $ascending ? -1 : 1;
            }

            if ($video1->title > $video2->title) {
                return $titleAscending ? 1 : -1;
            }

            if ($video1->title < $video2->title) {
                return $titleAscending ? -1 : 1;
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