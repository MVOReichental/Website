<?php
use de\mvo\Database;
use de\mvo\MustacheRenderer;
use de\mvo\router\Endpoints;
use de\mvo\router\Router;

try
{
	require_once __DIR__ . "/../bootstrap.php";

	Database::init();

	$router = new Router;

	$router->mapAll(Endpoints::get());

	if (isset($_SERVER["PATH_INFO"]))
	{
		$path = $_SERVER["PATH_INFO"];
	}
	else
	{
		$path = "/";
	}

	$target = $router->getMatchingTarget($path);
	if ($target === null)
	{
		http_response_code(404);
		$content = file_get_contents(VIEWS_ROOT . "/not-found.html");
	}
	else
	{
		$content = $target->call();
		if ($content === null)
		{
			exit;
		}
	}

	echo MustacheRenderer::render("main", array
	(
		"content" => $content,
		"currentYear" => date("Y")
	));
}
catch (Exception $exception)
{
	http_response_code(500);
	readfile(__DIR__ . "/error-500.html");
	error_log($exception);
}