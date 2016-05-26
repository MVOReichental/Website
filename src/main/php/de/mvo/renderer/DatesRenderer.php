<?php
namespace de\mvo\renderer;

use de\mvo\model\date\DateList;
use de\mvo\renderer\utils\MustacheRenderer;

class DatesRenderer extends AbstractRenderer
{
	const TYPE_HTML = "html";
	const TYPE_ICAL = "ical";
	const TYPE_PDF = "pdf";

	private $type;

	public function __construct($type)
	{
		$this->type = $type;
	}

	public function render()
	{
		$dates =  new DateList;

		switch ($this->type)
		{
			case self::TYPE_HTML:
				return MustacheRenderer::render("dates", array("dates" => $dates));
			case self::TYPE_ICAL:
				// Render iCal
				return null;
			case self::TYPE_PDF:
				// Render PDF
				return null;
		}
	}
}