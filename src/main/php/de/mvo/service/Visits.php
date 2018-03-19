<?php
namespace de\mvo\service;

use DateInterval;
use de\mvo\Date;
use de\mvo\model\visits\Stats;
use de\mvo\model\visits\Visit;
use de\mvo\TwigRenderer;
use Twig_Error;

class Visits extends AbstractService
{
    /**
     * @return string
     * @throws Twig_Error
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