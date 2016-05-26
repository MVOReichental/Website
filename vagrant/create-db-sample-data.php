<?php
use de\mvo\Database;

require_once __DIR__ . "/../bootstrap.php";

Database::init();

Database::query("DELETE FROM `dates`");
Database::query("DELETE FROM `locations`");

$locations = array
(
	array(48.7301, 8.39091, "Schulhof / Festhalle"),
	array(48.7298, 8.39214, "Grüner Baum"),
	array(48.7626, 8.33657, "Gernsbach - Schloßstrasse")
);

$dates = array
(
	array("yesterday 14:00", null, "Some event"),
	array("tomorrow 12:00", "tomorrow 13:00", "Another event"),
	array("Next monday 18:00", "Next monday 20:00", "Foo"),
	array(date("Y") . "-12-24 22:00", null, "Bar"),
	array(date("Y") . "-12-31 23:50", null, "Baz"),
	array("+1 hour", "+2 hours", "An event"),
	array("-15 minutes", "+45 minutes", "Some other event")
);

$query = Database::prepare("
	INSERT INTO `locations`
	SET
		`latitude` = :latitude,
		`longitude` = :longitude,
		`name` = :name
");

$locationIds = array();

foreach ($locations as $location)
{
	$query->execute(array
	(
		":latitude" => $location[0],
		":longitude" => $location[1],
		":name" => $location[2]
	));

	$locationIds[] = Database::lastInsertId();
}

$query = Database::prepare("
	INSERT INTO `dates`
	SET
		`startDate` = :startDate,
		`endDate` = :endDate,
		`title` = :title,
		`locationId` = :locationId
");

foreach ($dates as $date)
{
	$locationId = $locationIds[array_rand($locationIds)];

	$query->execute(array
	(
		":startDate" => date("Y-m-d H:i:s", strtotime($date[0])),
		"endDate" => ($date[1] === null ? null : date("Y-m-d H:i:s", strtotime($date[1]))),
		":title" => $date[2],
		":locationId" => $locationId
	));
}