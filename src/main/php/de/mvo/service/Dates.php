<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\date\Entry;
use de\mvo\MustacheRenderer;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class Dates extends AbstractService
{
	public function getHtml()
	{
		return MustacheRenderer::render("dates/page", array
		(
			"dates" => new DateList,
			"yearlyDates" => json_decode(file_get_contents(MODELS_ROOT . "/yearly-events.json"))
		));
	}

	public function getIcal()
	{
		$calendar = new Calendar($_SERVER["HTTP_HOST"]);

		$dates = new DateList;

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