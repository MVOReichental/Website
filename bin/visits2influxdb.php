#! /usr/bin/env php
<?php
use de\mvo\Config;
use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\visits\Visit;
use InfluxDB\Client;
use InfluxDB\Exception;
use InfluxDB\Point;

require_once __DIR__ . "/../bootstrap.php";

Database::init();

$date = new Date;
$date->setTime(0, 0, 0);
$date->sub(new DateInterval("P1D"));

$host = Config::getRequiredValue("influxdb", "host");
$port = Config::getValue("influxdb", "port", 8088);
$username = Config::getValue("influxdb", "username");
$password = Config::getValue("influxdb", "password");
$dbName = Config::getRequiredValue("influxdb", "database");

$client = new Client($host, $port, $username, $password);
$database = $client->selectDB($dbName);

$visits = Visit::getAtDate($date);

$guests = 0;
$users = 0;

foreach ($visits as $visit) {
    if ($visit->user === null) {
        $guests++;
    } else {
        $users++;
    }
}

$fields = array(
    "guests" => $guests,
    "users" => $users
);

$points = array(new Point("visitors", null, array(), $fields, $date->getTimestamp()));

for ($try = 1; $try <= 10; $try++) {
    try {
        $database->writePoints($points);
        break;
    } catch (Exception $e) {
        sleep(60);
    }
}