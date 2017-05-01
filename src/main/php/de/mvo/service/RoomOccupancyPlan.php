<?php
namespace de\mvo\service;

use DateInterval;
use de\mvo\Date;
use de\mvo\model\roomoccupancyplan\Entries;
use de\mvo\model\roomoccupancyplan\Entry;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;

class RoomOccupancyPlan extends AbstractService
{
    public function getCalendar()
    {
        return TwigRenderer::render("roomoccupancyplan/calendar");
    }

    public function getEntries()
    {
        if (!isset($_GET["start"]) or !isset($_GET["end"])) {
            http_response_code(400);
            return null;
        }

        $requestedStartDate = new Date($_GET["start"]);
        $requestedEndDate = new Date($_GET["end"]);
        $requestedEndDate->sub(new DateInterval("P1D"));// FullCalendar uses the exclusive date end

        $requestedStartWeekday = (int)$requestedStartDate->format("N");

        $entries = array();

        /**
         * @var $entry Entry
         */
        foreach (Entries::getInRange($requestedStartDate, $requestedEndDate) as $entry) {
            $date = clone $entry->date;

            $weekday = (int)$date->format("N");

            if ($entry->repeatWeekly or $entry->repeatTillDate !== null) {
                $date = clone $requestedStartDate;

                if ($weekday > $requestedStartWeekday) {
                    $date->add(new DateInterval(sprintf("P%dD", $weekday - $requestedStartWeekday)));
                } elseif ($weekday < $requestedStartWeekday) {
                    $date->sub(new DateInterval(sprintf("P%dD", $requestedStartWeekday - $weekday)));
                }
            }

            $startDate = clone $date;
            $endDate = clone $date;

            $startTime = explode(":", $entry->startTime);
            $endTime = explode(":", $entry->endTime);

            $startDate->setTime($startTime[0], $startTime[1], $startTime[2]);
            $endDate->setTime($endTime[0], $endTime[1], $endTime[2]);

            // End date might now be smaller than the start time (e.g. 23:00 - 01:00)
            if ($endDate < $startDate) {
                $endDate->add(new DateInterval("P1D"));// Modify date to make sure the end time is after the start time)
            }

            $entries[] = array
            (
                "id" => $entry->id,
                "date" => $entry->date->format("Y-m-d"),
                "start" => $startDate->format("c"),
                "end" => $endDate->format("c"),
                "title" => $entry->title,
                "repeatWeekly" => $entry->repeatWeekly,
                "repeatTillDate" => $entry->repeatTillDate === null ? null : $entry->repeatTillDate->format("Y-m-d")
            );
        }

        header("Content-Type: application/json");

        return json_encode($entries);
    }

    public function moveResizeEntry()
    {
        $entry = Entry::getById($this->params->id);

        if ($entry === null) {
            throw new NotFoundException;
        }

        if (!isset($_POST["start"]) or !isset($_POST["end"])) {
            http_response_code(400);
            return;
        }

        $startDate = new Date($_POST["start"]);
        $endDate = new Date($_POST["end"]);

        $entryWeekday = $entry->date->format("N");
        $newWeekday = $startDate->format("N");

        if ($newWeekday > $entryWeekday) {
            $entry->date->add(new DateInterval(sprintf("P%dD", $newWeekday - $entryWeekday)));
        } elseif ($newWeekday < $entryWeekday) {
            $entry->date->sub(new DateInterval(sprintf("P%dD", $entryWeekday - $newWeekday)));
        }

        $entry->startTime = $startDate->format("H:i:s");
        $entry->endTime = $endDate->format("H:i:s");

        $entry->save();
    }

    public function editEntry()
    {
        $entry = Entry::getById($this->params->id);

        if ($entry === null) {
            throw new NotFoundException;
        }

        if (!isset($_POST["title"]) or !isset($_POST["date"]) or !isset($_POST["start"]) or !isset($_POST["end"])) {
            http_response_code(400);
            return;
        }

        $entry->title = $_POST["title"];
        $entry->date = new Date($_POST["date"]);
        $entry->startTime = $_POST["start"];
        $entry->endTime = $_POST["end"];
        $entry->repeatWeekly = (bool)$_POST["repeatWeekly"];

        if (isset($_POST["repeatTillDate"]) and $_POST["repeatTillDate"] !== "") {
            $entry->repeatTillDate = new Date($_POST["repeatTillDate"]);
        } else {
            $entry->repeatTillDate = null;
        }

        $entry->save();
    }

    public function createEntry()
    {
        if (!isset($_POST["title"]) or !isset($_POST["date"]) or !isset($_POST["start"]) or !isset($_POST["end"])) {
            http_response_code(400);
            return;
        }

        $entry = new Entry;

        $entry->title = $_POST["title"];
        $entry->date = new Date($_POST["date"]);
        $entry->startTime = $_POST["start"];
        $entry->endTime = $_POST["end"];
        $entry->repeatWeekly = (bool)$_POST["repeatWeekly"];

        if (isset($_POST["repeatTillDate"]) and $_POST["repeatTillDate"] !== "") {
            $entry->repeatTillDate = new Date($_POST["repeatTillDate"]);
        }

        $entry->save();
    }
}