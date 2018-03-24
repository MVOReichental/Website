<?php
use de\mvo\Database;
use de\mvo\DBSessionHandler;
use de\mvo\model\users\User;
use de\mvo\model\visits\Visit;
use de\mvo\router\Endpoints;
use de\mvo\router\Router;
use de\mvo\service\exception\LoginException;
use de\mvo\service\exception\NotFoundException;
use de\mvo\service\exception\PermissionViolationException;
use de\mvo\TwigRenderer;

try {
    require_once __DIR__ . "/../bootstrap.php";

    Database::init();

    DBSessionHandler::start();

    TwigRenderer::init();

    Visit::track();

    Endpoints::map();

    $router = new Router;

    $router->mapAll(Endpoints::get());

    if (isset($_SERVER["PATH_INFO"])) {
        $path = $_SERVER["PATH_INFO"];
    } else {
        $path = "/";
    }

    try {
        $target = $router->getMatchingTarget($path);
        if ($target === null) {
            // Do not allow guessing internal pages
            if (explode("/", ltrim($path, "/"), 2)[0] == "internal" and User::getCurrent() === null) {
                throw new LoginException(LoginException::NOT_LOGGED_IN);
            }

            throw new NotFoundException;
        } else {
            echo $target->call();
        }
    } catch (LoginException $exception) {
        http_response_code(401);
        echo TwigRenderer::render("account/login", array
        (
            "url" => (isset($_GET["redirect"]) and $_GET["redirect"] != "") ? $_GET["redirect"] : $path,
            "requestToken" => ($exception->getType() == LoginException::REQUIRE_2FA_TOKEN or $exception->getType() == LoginException::INVALID_2FA_TOKEN),
            "errorMessage" => $exception->getLocalizedMessage()
        ));
    } catch (PermissionViolationException $exception) {
        http_response_code(403);
        echo TwigRenderer::render("permission-denied");
    } catch (NotFoundException $exception) {
        http_response_code(404);
        echo TwigRenderer::render("not-found");
    }
} catch (Exception $exception) {
    http_response_code(500);
    readfile(__DIR__ . "/error-500.html");
    error_log($exception);
}