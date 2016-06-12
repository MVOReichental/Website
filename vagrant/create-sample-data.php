<?php
use de\mvo\Database;
use de\mvo\model\permissions\Group;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\users\User;

require_once __DIR__ . "/../bootstrap.php";

Database::init();

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
	array("-15 minutes", "+45 minutes", "Some other event"),
	array("Tomorrow", null, "My event")
);

Database::query("DELETE FROM `dates`");
Database::query("DELETE FROM `locations`");

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

$user1 = new User;
$user1->username = "example";
$user1->firstName = "My";
$user1->lastName = "Example";

$user2 = new User;
$user2->username = "foobar";
$user2->firstName = "Foo";
$user2->lastName = "Bar";

$user3 = new User;
$user3->username = "test";
$user3->firstName = "Test";
$user3->lastName = "User";

$users = array($user1, $user2, $user3);

Database::query("DELETE FROM `users`");

$query = Database::prepare("
	INSERT INTO `users`
	SET
		`username` = :username,
		`firstName` = :firstName,
		`lastName` = :lastName
");

/**
 * @var $user User
 */
foreach ($users as $user)
{
	$query->execute(array
	(
		":username" => $user->username,
		":firstName" => $user->firstName,
		":lastName" => $user->lastName
	));

	$user->id = Database::lastInsertId();
}

$user1->setPassword("my");
$user2->setPassword("foo");
$user3->setPassword("test");

$root = new GroupList;

$rootGroup = new Group;
$rootGroup->title = "Musiker";
$rootGroup->permissions->append("group.musicians");
$rootGroup->permissions->append("some.other.permission");
$root->append($rootGroup);

$group1 = new Group;
$group1->title = "Group 1";
$group1->permissions->append("group.musicians.group1");
$group1->permissions->append("another.permission");
$rootGroup->addGroup($group1);

$group2 = new Group;
$group2->title = "Group 2";
$group2->permissions->append("group.musicians.group2");
$group2->permissions->append("permissions.2");
$rootGroup->addGroup($group2);

$group1->addUser($user1);
$group2->addUser($user2);
$group2->addUser($user3);

$rootGroup = new Group;
$rootGroup->title = "Vorstandschaft";
$rootGroup->permissions->append("group.vorstandschaft");
$rootGroup->permissions->append("some.other.permission");
$root->append($rootGroup);

$group1 = new Group;
$group1->title = "Group 3";
$group1->permissions->append("group.vorstandschaft.vorstand");
$group1->addUser($user1);
$rootGroup->addGroup($group1);

$root->save();