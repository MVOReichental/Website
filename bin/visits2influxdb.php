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

$dateString = $argv[1] ?? null;

if ($dateString) {
    $date = new Date($dateString);
} else {
    $date = new Date;
    $date->sub(new DateInterval("P1D"));
}

$date->setTime(0, 0, 0);

$host = Config::getRequiredValue("influxdb", "host");
$port = Config::getValue("influxdb", "port", 8086);
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
    if ($try > 1) {
        echo "Retrying in 60 seconds...\n";
        sleep(60);
    }

    try {
        $database->writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
        exit(0);
    } catch (Exception $e) {
        error_log($e);
    }
}

exit(1);