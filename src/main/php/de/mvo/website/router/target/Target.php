<?php
namespace de\mvo\website\router\target;

interface Target
{
	public function execute($method, $parameters);
}