<?php
namespace de\mvo\website\router;

use de\mvo\website\router\target\Page;
use de\mvo\website\router\target\Redirect;
use de\mvo\website\utils\HttpMethod;

class Routes
{
	public static function get()
	{
		return array
		(
			new Route(HttpMethod::GET, "/", new Redirect("/home")),
			new Route(HttpMethod::GET, "/home", new Page("Home"))
		);
	}
}