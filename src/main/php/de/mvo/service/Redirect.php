<?php
namespace de\mvo\service;

class Redirect extends AbstractService
{
	public function redirect($location, $code = 302)
	{
		foreach ($this->params as $key => $value)
		{
			$location = str_replace("%" . $key . "%", $value, $location);
		}

		header("Location: " . $location, true, $code);
		return null;
	}
}