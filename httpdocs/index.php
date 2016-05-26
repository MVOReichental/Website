<?php
use de\mvo\Database;
use de\mvo\renderer\StaticRenderer;
use de\mvo\renderer\utils\MustacheRenderer;
use de\mvo\router\Endpoints;
use de\mvo\router\Router;

require_once __DIR__ . "/../bootstrap.php";

Database::init();

$router = new Router;

$router->mapAll(Endpoints::get());

$match = $router->match($_SERVER["PATH_INFO"]);
if ($match === null)
{
	$renderer = new StaticRenderer("not-found");
}
else
{
	$renderer = $match->renderer;
}

$view = $renderer->render();

if (headers_sent())
{
	echo $view;
}
else
{
	echo MustacheRenderer::render("main", array
	(
		"view" => $view,
		"currentYear" => date("Y")
	));
}