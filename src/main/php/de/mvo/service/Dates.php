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
use Twig_Error;

class Dates extends AbstractService
{
    /**
     * @param bool $internal
     * @return string
     * @throws Twig_Error
     */
    public function getHtml($internal = false)
    {
        if ($internal) {
            $user = User::getCurrent();

            $dates = DateList::get()->visibleForUser($user);

            if (isset($_GET["groups"])) {
                $selectedGroups = array_filter(explode(" ", $_GET["groups"]));

                if (in_array("public", $selectedGroups)) {
                    $selectedGroups = array_diff($selectedGroups, array("__public__"));

                    $includePublic = true;
                } else {
                    $includePublic = false;
                }
            } else {
                $selectedGroups = array();
            }

            if (empty($selectedGroups)) {
                $includePublic = true;
            }

            $groups = array();

            foreach (Groups::getAll() as $group => $title) {
                if (!$user->hasPermission("dates.view." . $group)) {
                    continue;
                }

                $groups[$group] = array
                (
                    "title" => $title,
                    "active" => (empty($selectedGroups) or in_array($group, $selectedGroups))
                );
            }

            if (!empty($selectedGroups)) {
                $dates = $dates->getInGroups(new Groups($selectedGroups), $includePublic);
            }
        } else {
            $dates = DateList::get()->publiclyVisible();
            $user = null;
            $groups = null;
            $includePublic = false;
        }

        return TwigRenderer::render("dates/" . ($internal ? "page-internal" : "page"), array
        (
            "dates" => $dates,
            "yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json")),
            "allowEdit" => $internal and ($user === null ? false : $user->hasPermission("dates.edit")),
            "groups" => $groups,
            "includePublic" => $includePublic
        ));
    }

    public function getIcal($internal = false)
    {
        $calendar = new Calendar($_SERVER["HTTP_HOST"]);

        $user = User::getCurrent();
        if ($internal and $user !== null) {
            $dates = DateList::get()->visibleForUser($user);
        } else {
            $dates = DateList::get()->publiclyVisible();
        }

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

    public function getAutoCompletionList()
    {
        $groupedEntries = array();

        foreach (DateList::getAll()->visibleForUser(User::getCurrent()) as $entry) {
            $entry->name = $entry->title;// Required for Bootstrap-3-Typeahead
            $groupedEntries[$entry->title] = $entry;
        }

        header("Content-Type: application/json");
        echo json_encode(array_values($groupedEntries));
        return null;
    }

    /**
     * @return null
     * @throws NotFoundException
     */
    public function deleteEntry()
    {
        $entry = Entry::getById($this->params->id);
        if ($entry === null) {
            throw new NotFoundException;
        }

        $entry->delete();

        return null;
    }

    /**
     * @return null
     * @throws NotFoundException
     */
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

        header("Location: /internal/dates");
        return null;
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function showCreateEntryForm()
    {
        return TwigRenderer::render("dates/edit", array
        (
            "groups" => Groups::getAll()
        ));
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws Twig_Error
     */
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