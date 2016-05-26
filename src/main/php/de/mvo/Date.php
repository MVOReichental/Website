<?php
namespace de\mvo;

use DateTime;

class Date extends DateTime
{
	public function hasTime()
	{
		return ($this->format("H:i:s") != "00:00:00");
	}

	public function humanReadableDate()
	{
		$date = $this->format("j.m.Y");

		$weekday = self::getWeekdayName($this->format("N"));

		return $weekday . ", " . $date;
	}

	public function humanReadableTime()
	{
		return $this->format("H:i");
	}

	public function __toString()
	{
		return $this->format("c");
	}

	public static function getWeekdayName($weekday)
	{
		switch ($weekday)
		{
			case 1:
				return "Mo";
			case 2:
				return "Di";
			case 3:
				return "Mi";
			case 4:
				return "Do";
			case 5:
				return "Fr";
			case 6:
				return "Sa";
			case 7:
				return "So";
		}

		return null;
	}
}