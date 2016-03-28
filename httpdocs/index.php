<?php
use de\mvo\website\router\Router;
use de\mvo\website\router\Routes;

require_once __DIR__ . "/../bootstrap.php";

$router = new Router;
$router->mapAll(Routes::get());

$router->execute();