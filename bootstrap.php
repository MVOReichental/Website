<?php
require_once __DIR__ . "/vendor/autoload.php";

define("APP_ROOT", __DIR__);
define("RESOURCES_ROOT", __DIR__ . "/src/main/resources");
define("HTTPDOCS_ROOT", __DIR__ . "/httpdocs");

define("MODELS_ROOT", RESOURCES_ROOT . "/models");
define("VIEWS_ROOT", RESOURCES_ROOT . "/views");
define("PROFILE_PICTURES_ROOT", RESOURCES_ROOT . "/profile-pictures");
define("UPLOADS_ROOT", RESOURCES_ROOT . "/uploads");
define("PICTURES_ROOT", HTTPDOCS_ROOT . "/pictures");
define("MAIL_QUEUE_ROOT", RESOURCES_ROOT . "/mail-queue");

define("APP_NAMESPACE", "de\\mvo\\website");

if (file_exists(APP_ROOT . "/version")) {
    define("APP_VERSION", trim(file_get_contents(APP_ROOT . "/version")));
} else {
    define("APP_VERSION", null);
}