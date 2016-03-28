<?php
namespace de\mvo\website\page;

class NotFound extends AbstractPage
{
	public function init()
	{
		$this->setTitle("Seite nicht gefunden");

		header("HTTP/1.1 404 Not Found");
	}

	public function get()
	{
		echo "not found...";
	}
}