<?php
namespace de\mvo\service;

class Redirect extends AbstractService
{
	public function redirect($location, $code = 301)
	{
		header("Location: " . $location, true, $code);
		return null;
	}
}