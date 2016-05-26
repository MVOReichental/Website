<?php
namespace de\mvo\router;

use de\mvo\renderer\Renderer;

class Match
{
	public $params;
	/**
	 * @var Renderer
	 */
	public $renderer;

	public function __construct($params, Renderer $renderer)
	{
		$this->params = $params;
		$this->renderer = $renderer;
	}
}