<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\MustacheRenderer;

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
		// TODO: Build iCal
		return null;
	}
}