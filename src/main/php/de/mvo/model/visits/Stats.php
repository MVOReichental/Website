<?php
namespace de\mvo\model\visits;

use DateInterval;
use de\mvo\Date;

class Stats
{
    /**
     * @var int
     */
    public $guests = 0;
    /**
     * @var int
     */
    public $users = 0;

    public static function today()
    {
        $stats = new self;

        $stats->count(Visit::getAtDate(new Date));

        return $stats;
    }

    public static function yesterday()
    {
        $stats = new self;

        $stats->count(Visit::getAtDate((new Date)->sub(new DateInterval("P1D"))));

        return $stats;
    }

    public static function currentWeek()
    {
        $startDate = new Date("monday this week");

        $endDate = clone $startDate;
        $endDate->add(new DateInterval("P6D"));// Add 6 days to reach sunday

        $stats = new self;

        $stats->count(Visit::getInDateRange($startDate, $endDate));

        return $stats;
    }

    public static function previousWeek()
    {
        $startDate = new Date("monday this week");
        $startDate->sub(new DateInterval("P1W"));

        $endDate = clone $startDate;
        $endDate->add(new DateInterval("P6D"));// Add 6 days to reach sunday

        $stats = new self;

        $stats->count(Visit::getInDateRange($startDate, $endDate));

        return $stats;
    }

    public static function currentMonth()
    {
        $startDate = new Date("first day of this month");
        $endDate = new Date("last day of this month");

        $stats = new self;

        $stats->count(Visit::getInDateRange($startDate, $endDate));

        return $stats;
    }

    public static function previousMonth()
    {
        $startDate = new Date("first day of previous month");
        $endDate = new Date("last day of previous month");

        $stats = new self;

        $stats->count(Visit::getInDateRange($startDate, $endDate));

        return $stats;
    }

    /**
     * @param Visit[] $visits
     */
    public function count(array $visits)
    {
        foreach ($visits as $visit) {
            if ($visit->user === null) {
                $this->guests++;
            } else {
                $this->users++;
            }
        }
    }
}