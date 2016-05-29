<?php
namespace de\mvo\service;

abstract class AbstractService
{
	public $params;

	public function setParams($params)
	{
		$this->params = $params;
	}
}