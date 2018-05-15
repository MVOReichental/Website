<?php
namespace de\mvo\service;

use de\mvo\Date;
use de\mvo\model\date\DateList;
use de\mvo\model\date\Entry;
use de\mvo\model\date\Groups;
use de\mvo\model\date\Location;
use de\mvo\model\users\Groups as UserGroups;
use de\mvo\model\users\User;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Twig_Error;

class Dates extends AbstractService
{
    /**
     * @return string
     * @throws Twig_Error
     */
    public function getPublicHtml()
    {
        $dates = DateList::get()->publiclyVisible();

        return TwigRenderer::render("dates/page", array
        (
            "dates" => $dates,
            "yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json"))
        ));
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function getInternalHtml()
    {
        $groups = null;
        $includePublic = false;

        $user = User::getCurrent();

        $dates = DateList::getAll()->visibleForUser($user);

        $years = $dates->getYears();
        rsort($years, SORT_NUMERIC);

        $activeYear = $this->params->year ?? null;

        if ($activeYear) {
            $dates = $dates->withYear($activeYear);
        } else {
            $dates = $dates->startingAt(new Date);
            $activeYear = null;
        }

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

        foreach (UserGroups::getAll() as $group => $title) {
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

        return TwigRenderer::render("dates/page-internal", array
        (
            "dates" => $dates,
            "yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json")),
            "allowEdit" => $user->hasPermission("dates.edit"),
            "years" => $years,
            "activeYear" => $activeYear,
            "groups" => $groups,
            "includePublic" => $includePublic
        ));
    }

    public function getPublicIcal()
    {
        $calendar = new Calendar($_SERVER["HTTP_HOST"]);

        $dates = DateList::getAllPublic();

        /**
         * @var $date Entry
         */
        foreach ($dates as $date) {
            $calendar->addComponent($date->getIcalEvent());
        }

        header("Content-Type: text/calendar; charset=utf-8");
        echo $calendar->render();
        return null;
    }

    public function getInternalIcalWithToken()
    {
        $user = User::getByDatesToken($this->params->token);

        if ($user === null) {
            http_response_code(401);
            return null;
        }

        $calendar = new Calendar($_SERVER["HTTP_HOST"]);

        $dates = DateList::getAll()->visibleForUser($user);

        /**
         * @var $date Entry
         */
        foreach ($dates as $date) {
            $event = new Event;

            $event->setDtStart($date->startDate);
            $event->setDtEnd($date->endDate);

            $event->setUseUtc(false);
            $event->setNoTime(!$date->startDate->hasTime());

            $event->setSummary($date->title);
            $event->setDescription($date->description);

            $calendar->addComponent($date->getIcalEvent());
        }

        header("Content-Type: text/calendar; charset=utf-8");
        echo $calendar->render();
        return null;
    }

    public function generateToken()
    {
        User::getCurrent()->generateDatesToken();

        header("Location: /internal/dates");
        return null;
    }

    public function getAutoCompletionList()
    {
        $titles = array();
        $locations = array();

        /**
         * @var $entry Entry
         */
        foreach (DateList::getAll()->visibleForUser(User::getCurrent()) as $entry) {
            $titles[$entry->title] = array
            (
                "name" => $entry->title
            );

            $location = $entry->location;
            if ($location !== null) {
                $locations[$location->name] = array
                (
                    "name" => $location->name
                );
            }
        }

        header("Content-Type: application/json");
        echo json_encode(array
        (
            "titles" => array_values($titles),
            "locations" => array_values($locations)
        ));
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
                if (!UserGroups::hasGroup($group)) {
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
            "groups" => UserGroups::getAll()
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
            "groups" => UserGroups::getAll(),
            "entry" => $entry
        ));
    }
}