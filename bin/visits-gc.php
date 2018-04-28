#! /usr/bin/env php
<?php
use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\visits\Visit;

require_once __DIR__ . "/../bootstrap.php";

Database::init();

$deleteDate = new Date;
$deleteDate->sub(new DateInterval("P2M"));

Visit::cleanup($deleteDate);

$removeIpDate = new Date;
$removeIpDate->sub(new DateInterval("P1D"));

Visit::removeIps($removeIpDate);