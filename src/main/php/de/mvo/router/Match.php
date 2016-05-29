<?php
namespace de\mvo\router;

class Match
{
	/**
	 * @var Target
	 */
	public $target;

	public function __construct($params, Target $target)
	{
		$target->params = $params;
		$this->target = $target;
	}
}