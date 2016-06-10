<?php
namespace de\mvo\model\notedirectory;

use ArrayObject;
use de\mvo\Database;

class Programs extends ArrayObject
{
	public static function getAll()
	{
		$query = Database::query("
			SELECT *
			FROM `notedirectoryprograms`
			ORDER BY `year` DESC, `title` ASC
		");

		$programs = new self;

		while ($program = $query->fetchObject(Program::class))
		{
			$programs->append($program);
		}

		return $programs;
	}

	public function getByYear($year)
	{
		$programs = new self;

		/**
		 * @var $program Program
		 */
		foreach ($this as $program)
		{
			if ($program->year != $year)
			{
				continue;
			}

			$programs->append($program);
		}

		return $programs;
	}

	public function getGroupedByYear()
	{
		$group = array();

		/**
		 * @var $program Program
		 */
		foreach ($this as $program)
		{
			$group[$program->year] = $this->getByYear($program->year);
		}

		$years = new ArrayObject;

		/**
		 * @var $programs Programs
		 */
		foreach ($group as $year => $programs)
		{
			$yearObject = new Year;

			$yearObject->year = $year;
			$yearObject->programs = $programs;

			$years->append($yearObject);
		}

		return $years;
	}
}