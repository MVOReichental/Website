<?php
namespace de\mvo\router;

use de\mvo\renderer\Renderer;

class Endpoint
{
	/**
	 * @var string
	 */
	public $method;
	/**
	 * @var string
	 */
	public $path;
	/**
	 * @var Renderer
	 */
	public $renderer;

	public function __construct($method, $path, Renderer $renderer)
	{
		$this->method = $method;
		$this->path = $path;
		$this->renderer = $renderer;
	}
}