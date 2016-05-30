<?php
use de\mvo\Database;
use de\mvo\model\User;
use de\mvo\MustacheRenderer;
use de\mvo\router\Endpoints;
use de\mvo\router\Router;
use de\mvo\service\exception\LoginException;
use de\mvo\service\exception\PermissionViolationException;

try
{
	require_once __DIR__ . "/../bootstrap.php";

	Database::init();

	session_start();

	$router = new Router;

	$router->mapAll(new Endpoints);

	if (isset($_SERVER["PATH_INFO"]))
	{
		$path = $_SERVER["PATH_INFO"];
	}
	else
	{
		$path = "/";
	}

	$useInternalMainView = false;

	$internalPageRequested = (substr(ltrim($path, "/"), 0, 6) == "intern");

	try
	{
		$target = $router->getMatchingTarget($path);
		if ($target === null)
		{
			// Do not allow guessing internal pages
			if ($internalPageRequested and User::getCurrent() === null)
			{
				throw new LoginException(LoginException::NOT_LOGGED_IN);
			}

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
	}
	catch (LoginException $exception)
	{
		http_response_code(401);
		$content = MustacheRenderer::render("account/login", array
		(
			"url" => $path,
			"errorMessage" => $exception->getLocalizedMessage()
		));

		$useInternalMainView = true;
	}
	catch (PermissionViolationException $exception)
	{
		http_response_code(403);
		$content = file_get_contents(VIEWS_ROOT . "/permission-denied.html");
	}

	if ($internalPageRequested and User::getCurrent() !== null)
	{
		$useInternalMainView = true;
	}

	if ($useInternalMainView)
	{
		echo MustacheRenderer::render("main-intern", array
		(
			"content" => $content,
			"currentYear" => date("Y"),
			"user" => User::getCurrent(),
			"loggedIn" => (User::getCurrent() !== null)
		));
	}
	else
	{
		echo MustacheRenderer::render("main", array
		(
			"content" => $content,
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