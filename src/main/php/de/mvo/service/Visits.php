<?php
namespace de\mvo\service;

use de\mvo\model\visits\Stats;
use de\mvo\model\visits\Visit;
use de\mvo\TwigRenderer;

class Visits extends AbstractService
{
    public function getPage()
    {
        return TwigRenderer::render("admin/visits", array
        (
            "currentUsers" => count(Visit::getCurrentVisits()),
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
}