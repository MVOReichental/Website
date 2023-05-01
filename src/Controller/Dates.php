<?php
namespace App\Controller;

use App\Repository\DateRepository;
use Eluceo\iCal\Component\Calendar;
use App\Date;
use App\Entity\date\DateList;
use App\Entity\date\Entry;
use App\Entity\date\Groups;
use App\Entity\users\Groups as UserGroups;
use App\Entity\users\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: "Dates::", methods: ["GET"])]
class Dates extends AbstractController
{
    public function __construct(private readonly DateRepository $dateRepository)
    {
    }

    #[Route("/termine", name: "getPublicHtml")]
    #[Template("dates/page.twig")]
    public function getPublicHtml(): array
    {
        return [
            "dates" => $this->dateRepository->get(),
            "yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json"))
        ];
    }

    #[Route("/internal/dates", name: "getInternalHtml")]
    #[Template("dates/page-internal.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getInternalHtml(): array
    {
        return $this->getInternalHtmlForYear(null);
    }

    #[Route("/internal/dates/{year}", name: "getInternalHtmlForYear", requirements: ["year" => "\d{4}"])]
    #[Template("dates/page-internal.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getInternalHtmlForYear(?int $year): array
    {
        $includePublic = false;

        $user = User::getCurrent();

        $dates = $this->dateRepository->get(true)->visibleForUser($user);

        $years = $dates->getYears();
        rsort($years, SORT_NUMERIC);

        if ($year) {
            $dates = $dates->withYear($year);
        } else {
            $dates = $dates->startingAt(new Date);
            $year = null;
        }

        if (isset($_GET["groups"])) {
            $selectedGroups = array_filter(explode(" ", $_GET["groups"]));

            if (in_array("public", $selectedGroups)) {
                $selectedGroups = array_diff($selectedGroups, array("__public__"));

                $includePublic = true;
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

        return [
            "dates" => $dates,
            "yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json")),
            "allowEdit" => $user->hasPermission("dates.edit"),
            "years" => $years,
            "activeYear" => $year,
            "groups" => $groups,
            "includePublic" => $includePublic
        ];
    }

    #[Route("/termine.ics", name: "getPublicIcal")]
    public function getPublicIcal(): Response
    {
        $calendar = new Calendar($_SERVER["HTTP_HOST"]);

        $calendar->setName("MVO Termine");
        $calendar->setDescription("Termine vom Musikverein Orgelfels Reichental e.V.");
        $calendar->setPublishedTTL("PT1H");

        $dates = DateList::getAllPublic();

        /**
         * @var $date Entry
         */
        foreach ($dates as $date) {
            $calendar->addComponent($date->getIcalEvent());
        }

        return new Response($calendar->render(), 200, [
            "Content-Type" => "text/calendar; charset=utf-8"
        ]);
    }

    #[Route("/internal/dates/{token}.ics", name: "getInternalIcalWithToken")]
    public function getInternalIcalWithToken(string $token): Response
    {
        $user = User::getByDatesToken($token);

        if ($user === null) {
            throw new AccessDeniedHttpException;
        }

        $calendar = new Calendar($_SERVER["HTTP_HOST"]);

        $calendar->setName("MVO Termine");
        $calendar->setDescription("Termine vom Musikverein Orgelfels Reichental e.V.");
        $calendar->setPublishedTTL("PT1H");

        $dates = DateList::getAll()->visibleForUser($user);

        /**
         * @var $date Entry
         */
        foreach ($dates as $date) {
            $calendar->addComponent($date->getIcalEvent());
        }

        return new Response($calendar->render(), 200, [
            "Content-Type" => "text/calendar; charset=utf-8"
        ]);
    }

    #[Route("/internal/dates/generate-token", name: "generateToken")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function generateToken(): Response
    {
        User::getCurrent()->generateDatesToken();

        return $this->redirectToRoute("Dates::getInternalHtml");
    }

    #[Route("/internal/dates/autocompletion", name: "getAutoCompletionList")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getAutoCompletionList(): Response
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

        return $this->json([
            "titles" => array_values($titles),
            "locations" => array_values($locations)
        ]);
    }

    #[Route("/internal/dates/{id}", name: "deleteEntry", methods: ["DELETE"])]
    #[IsGranted("ROLE_DATES_EDITOR")]
    public function deleteEntry(int $id): Response
    {
        $entry = Entry::getById($id);
        if ($entry === null) {
            throw new NotFoundHttpException;
        }

        $entry->delete();

        return new Response("OK");
    }

    #[Route("/internal/dates", name: "saveEntry", methods: ["POST"])]
    #[IsGranted("ROLE_DATES_EDITOR")]
    public function saveEntry(): Response
    {
        if (isset($_POST["id"])) {
            $entry = Entry::getById($_POST["id"]);
            if ($entry === null) {
                throw new NotFoundHttpException;
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

        return $this->redirectToRoute("Dates::getInternalHtml");
    }

    #[Route("/internal/dates/create", name: "showCreateEntryForm")]
    #[Template("dates/edit.twig")]
    #[IsGranted("ROLE_DATES_EDITOR")]
    public function showCreateEntryForm(): array
    {
        return [
            "groups" => UserGroups::getAll()
        ];
    }

    #[Route("/internal/dates/edit/{id}", name: "showEditEntryForm")]
    #[Template("dates/edit.twig")]
    #[IsGranted("ROLE_DATES_EDITOR")]
    public function showEditEntryForm(int $id): array
    {
        $entry = Entry::getById($id);
        if ($entry === null) {
            throw new NotFoundHttpException;
        }

        return [
            "groups" => UserGroups::getAll(),
            "entry" => $entry
        ];
    }
}