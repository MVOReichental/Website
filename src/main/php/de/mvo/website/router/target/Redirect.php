<?php
namespace de\mvo\website\router\target;

class Redirect extends AbstractTarget
{
	public $target;
	public $code;

	public function __construct($target, $code = 301)
	{
		$this->target = $target;
		$this->code = $code;
	}

	public function execute($method, $parameters)
	{
		header("Location: " . $this->target, true, $this->code);
	}
}