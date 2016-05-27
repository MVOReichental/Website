<?php
use de\mvo\Database;
use de\mvo\renderer\NotFoundRenderer;
use de\mvo\renderer\utils\MustacheRenderer;
use de\mvo\router\Endpoints;
use de\mvo\router\Router;

try
{
	require_once __DIR__ . "/../bootstrap.php";

	Database::init();

	$router = new Router;

	$router->mapAll(Endpoints::get());

	$match = $router->match($_SERVER["PATH_INFO"]);
	if ($match === null)
	{
		$renderer = new NotFoundRenderer;
	}
	else
	{
		$renderer = $match->renderer;
	}

	$view = $renderer->render();
	if ($view === null)
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
}
catch (Exception $exception)
{
	http_response_code(500);
	readfile(__DIR__ . "/error-500.html");
	error_log($exception);
}