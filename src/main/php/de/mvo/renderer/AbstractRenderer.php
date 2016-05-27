<?php
namespace de\mvo\renderer;

abstract class AbstractRenderer implements Renderer
{
	public $params;

	public function setParams($params)
	{
		$this->params = $params;
	}
}