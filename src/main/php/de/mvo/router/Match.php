<?php
namespace de\mvo\router;

use de\mvo\renderer\Renderer;

class Match
{
	/**
	 * @var Renderer
	 */
	public $renderer;

	public function __construct($params, Renderer $renderer)
	{
		$renderer->setParams($params);

		$this->renderer = $renderer;
	}
}