<?php
use de\mvo\router\Endpoints;
use de\mvo\router\Router;
use de\mvo\router\Target;

require_once __DIR__ . "/../bootstrap.php";

$router = new Router;

$router->mapAll(Endpoints::get());

$match = $router->match($_SERVER["PATH_INFO"]);
if (!$match)
{
	header("HTTP/1.1 404 Not Found");
	echo "Endpoint not found";
	exit;
}

/**
 * @var $target Target
 */
$target = $match["target"];

$classInstance = new $target->class;

if (!method_exists($classInstance, $target->method))
{
	header("HTTP/1.1 500 Internal Server Error");
	echo "Method not found";
	exit;
}

header("Content-Type: application/json");
echo json_encode($classInstance->{$target->method}());