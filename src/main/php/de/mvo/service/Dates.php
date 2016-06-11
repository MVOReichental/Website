<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\date\Entry;
use de\mvo\model\users\User;
use de\mvo\TwigRenderer;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class Dates extends AbstractService
{
	public function getHtml($intern = false)
	{
		return TwigRenderer::render("dates/" . ($intern ? "page-intern" : "page"), array
		(
			"dates" => new DateList($intern ? User::getCurrent() : null),
			"yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json"))
		));
	}

	public function getIcal($intern = false)
	{
		$calendar = new Calendar($_SERVER["HTTP_HOST"]);

		$dates = new DateList($intern ? User::getCurrent() : null);

		/**
		 * @var $date Entry
		 */
		foreach ($dates as $date)
		{
			$event = new Event;

			$event->setDtStart($date->startDate);
			$event->setDtEnd($date->endDate);

			$event->setNoTime(!$date->startDate->hasTime());

			$event->setSummary($date->title);

			$calendar->addComponent($event);
		}

		header("Content-Type: text/calendar; charset=utf-8");
		echo $calendar->render();
		return null;
	}
}