<?php
namespace de\mvo\service;

class StaticView extends AbstractService
{
	public function get($filename)
	{
		return file_get_contents(VIEWS_ROOT . "/" . $filename . ".html");
	}
}