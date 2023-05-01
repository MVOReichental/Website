<?php
namespace App\Controller;

use Exception;
use GuzzleHttp\Client;
use ICal\Event;
use ICal\ICal;
use App\Config;
use App\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class RoomOccupancyPlan extends AbstractController
{
    /**
     * @return string
     * @throws Error
     */
    public function getCalendar()
    {
        return TwigRenderer::render("roomoccupancyplan/calendar");
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getEntries()
    {
        if (!isset($_GET["start"]) or !isset($_GET["end"])) {
            http_response_code(400);
            return null;
        }

        $filename = DATA_ROOT . "/roomoccupancyplan.serialized";
        $ical = null;

        if (!file_exists($filename) or filemtime($filename) < time() - Config::getValue("roomoccupancyplan", "ttl", 3600)) {
            try {
                $client = new Client(array("timeout" => 10));

                $response = $client->get(Config::getRequiredValue("roomoccupancyplan", "url"));

                if ($response->getStatusCode() == 200) {
                    $ical = new ICal;
                    $ical->initString($response->getBody()->getContents());

                    file_put_contents($filename, serialize($ical));
                }
            } catch (Exception $exception) {
                error_log($exception);
            }
        }

        if ($ical === null and file_exists($filename)) {
            $ical = unserialize(file_get_contents($filename));
        }

        // $ical is null if the cache file does not exist and the calendar can't be fetched
        // in that case just return an empty list
        if ($ical == null) {
            $events = array();
        } else {
            $events = $ical->eventsFromRange($_GET["start"], $_GET["end"]);
        }

        $entries = array();

        /**
         * @var $event Event
         */
        foreach ($events as $event) {
            $text = $event->summary;

            if ($event->location !== null and $event->location !== "") {
                $text = sprintf("%s\nOrt: %s", $text, $event->location);
            }

            $entries[] = array
            (
                "id" => $event->uid,
                "start" => $event->dtstart,
                "end" => $event->dtend,
                "title" => $text
            );
        }

        header("Content-Type: application/json");

        return json_encode($entries);
    }
}