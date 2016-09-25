<?php
namespace de\mvo\service;

use de\mvo\Date;
use de\mvo\model\date\DateList;
use de\mvo\model\date\Entry;
use de\mvo\model\date\Groups;
use de\mvo\model\date\Location;
use de\mvo\model\users\User;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class Dates extends AbstractService
{
    public function getHtml($internal = false)
    {
        $user = ($internal ? User::getCurrent() : null);

        $dates = DateList::get($user);

        if ($user === null) {
            $groups = null;
        } else {
            if (isset($this->params->groups)) {
                $selectedGroups = array_filter(explode("+", $this->params->groups));
            } else {
                $selectedGroups = array();
            }

            $groups = array();

            foreach (Groups::getAll() as $group => $title) {
                if (!$user->hasPermission("dates.view." . $group)) {
                    continue;
                }

                $groups[$group] = array
                (
                    "title" => $title,
                    "active" => false
                );

                $newSelectedGroups = $selectedGroups;

                $enabledGroupIndex = array_search($group, $newSelectedGroups);
                if ($enabledGroupIndex === false) {
                    $newSelectedGroups[] = $group;
                } else {
                    unset($newSelectedGroups[$enabledGroupIndex]);
                    $groups[$group]["active"] = true;
                }

                sort($newSelectedGroups);

                $groups[$group]["url"] = "internal/dates/" . implode("+", array_unique($newSelectedGroups));
            }

            foreach ($selectedGroups as $group) {
                if (!isset($groups[$group])) {
                    unset($groups[$group]);
                }
            }

            if (!empty($selectedGroups)) {
                $dates = $dates->getInGroups(new Groups($selectedGroups));
            }
        }

        return TwigRenderer::render("dates/" . ($internal ? "page-internal" : "page"), array
        (
            "dates" => $dates,
            "yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json")),
            "allowEdit" => $user === null ? false : $user->hasPermission("dates.edit"),
            "groups" => $groups
        ));
    }

    public function getIcal($internal = false)
    {
        $calendar = new Calendar($_SERVER["HTTP_HOST"]);

        $dates = DateList::get($internal ? User::getCurrent() : null);

        /**
         * @var $date Entry
         */
        foreach ($dates as $date) {
            $event = new Event;

            $event->setDtStart($date->startDate);
            $event->setDtEnd($date->endDate);

            $event->setNoTime(!$date->startDate->hasTime());

            $event->setSummary($date->title);
            $event->setDescription($date->description);

            $calendar->addComponent($event);
        }

        header("Content-Type: text/calendar; charset=utf-8");
        echo $calendar->render();
        return null;
    }

    public function deleteEntry()
    {
        $entry = Entry::getById($this->params->id);
        if ($entry === null) {
            throw new NotFoundException;
        }

        $entry->delete();

        return null;
    }

    public function saveEntry()
    {
        if (isset($_POST["id"])) {
            $entry = Entry::getById($_POST["id"]);
            if ($entry === null) {
                throw new NotFoundException;
            }
        } else {
            $entry = new Entry;
        }

        $date = new Date($_POST["date"]);

        $endTime = explode(":", $_POST["endTime"], 2);
        if (count($endTime) == 2) {
            $entry->endDate = clone $date;
            $entry->endDate->setTime($endTime[0], $endTime[1], 0);
        } else {
            $entry->endDate = null;
        }

        $entry->startDate = clone $date;

        $startTime = explode(":", $_POST["startTime"], 2);
        if (count($startTime) == 2) {
            $entry->startDate->setTime($startTime[0], $startTime[1], 0);
        }

        if (isset($_POST["title"])) {
            $entry->title = $_POST["title"];
        } else {
            $entry->title = null;
        }

        if (isset($_POST["description"]) and $_POST["description"] != "") {
            $entry->description = $_POST["description"];
        } else {
            $entry->description = null;
        }

        if (isset($_POST["location"]) and $_POST["location"] != "") {
            $entry->location = Location::getByName($_POST["location"]);
            if ($entry->location === null) {
                $entry->location = new Location;
                $entry->location->name = $_POST["location"];
                $entry->location->save();
            }
        } else {
            $entry->location = null;
        }

        $entry->highlight = (isset($_POST["highlight"]) and $_POST["highlight"]);
        $entry->isPublic = (isset($_POST["public"]) and $_POST["public"]);

        $entry->groups = new Groups;

        if (isset($_POST["groups"]) and is_array($_POST["groups"])) {
            foreach ($_POST["groups"] as $group) {
                if (!isset(Groups::getAll()[$group])) {
                    continue;
                }

                $entry->groups->append($group);
            }
        }

        $entry->save();

        return $this->getHtml(true);
    }

    public function showCreateEntryForm()
    {
        return TwigRenderer::render("dates/edit", array
        (
            "groups" => Groups::getAll()
        ));
    }

    public function showEditEntryForm()
    {
        $entry = Entry::getById($this->params->id);
        if ($entry === null) {
            throw new NotFoundException;
        }

        return TwigRenderer::render("dates/edit", array
        (
            "groups" => Groups::getAll(),
            "entry" => $entry
        ));
    }
}