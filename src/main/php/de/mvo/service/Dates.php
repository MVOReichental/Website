<?php
namespace de\mvo\service;

class Dates
{
	public function getYears()
	{
		return array
		(
			2010,
			2011,
			2012,
			2013,
			2014,
			2015,
			2016
		);
	}

	public function getDatesForYear()
	{
		return array();
	}

	public function getCurrentDates()
	{
		return array
		(
			array
			(
				"startDate" => "2016-06-01 12:00:00",
				"endDate" => null,
				"event" => "Some event",
				"location" => "Here"
			),
			array
			(
				"startDate" => "2016-06-02 14:00:00",
				"endDate" => "2016-06-02 16:00:00",
				"event" => "Another event",
				"location" => "Somewhere"
			)
		);
	}
}