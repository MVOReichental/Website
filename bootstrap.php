<?php
require_once __DIR__ . "/vendor/autoload.php";

define("APP_ROOT", __DIR__);
define("DATA_ROOT", __DIR__ . "/data");
define("RESOURCES_ROOT", __DIR__ . "/src/main/resources");

define("MODELS_ROOT", RESOURCES_ROOT . "/models");
define("VIEWS_ROOT", RESOURCES_ROOT . "/views");
define("FORMS_ROOT", DATA_ROOT . "/forms");
define("PROFILE_PICTURES_ROOT", DATA_ROOT . "/profile-pictures");
define("TWIG_CACHE_ROOT", DATA_ROOT . "/twig-cache");
define("UPLOADS_ROOT", DATA_ROOT . "/uploads");
define("PICTURES_ROOT", DATA_ROOT . "/pictures");
define("MAIL_QUEUE_ROOT", DATA_ROOT . "/mail-queue");

define("APP_NAMESPACE", "de\\mvo\\website");

if (file_exists(APP_ROOT . "/version")) {
    define("APP_VERSION", trim(file_get_contents(APP_ROOT . "/version")));
} else {
    define("APP_VERSION", null);
}