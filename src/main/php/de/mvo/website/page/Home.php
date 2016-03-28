<?php
namespace de\mvo\website\page;

class Home extends AbstractPage
{
	public function init()
	{
		$this->setTitle("Home");
	}

	public function get()
	{
		readfile(PAGE_CONTENT_ROOT . "/home.html");
	}
}