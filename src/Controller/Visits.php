<?php
namespace App\Controller;

use DateInterval;
use App\Date;
use App\Entity\visits\Stats;
use App\Entity\visits\Visit;
use App\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class Visits extends AbstractController
{
    /**
     * @return string
     * @throws Error
     */
    public function getPage()
    {
        return TwigRenderer::render("admin/visits", array
        (
            "currentVisits" => count(Visit::getCurrentVisits()),
            "stats" => array
            (
                "today" => Stats::today(),
                "yesterday" => Stats::yesterday(),
                "currentWeek" => Stats::currentWeek(),
                "previousWeek" => Stats::previousWeek(),
                "currentMonth" => Stats::currentMonth(),
                "previousMonth" => Stats::previousMonth()
            )
        ));
    }

    public function getChartData()
    {
        $startDate = new Date;
        $startDate->sub(new DateInterval("P1Y"));

        $data = array
        (
            "guests" => array(),
            "users" => array()
        );

        foreach (Visit::getInDateRange($startDate, new Date) as $visit) {
            $series = $visit->user === null ? "guests" : "users";
            $key = $visit->date->format("Y-m-d");

            if (!isset($data[$series][$key])) {
                $data[$series][$key] = array($visit->date->format("U") * 1000, 0);
            }

            $data[$series][$key][1]++;
        }

        foreach ($data as &$seriesData) {
            ksort($seriesData);
            $seriesData = array_values($seriesData);
        }

        header("Content-Type: application/json");

        return json_encode($data);
    }
}