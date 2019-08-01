#! /usr/bin/env php
<?php
use de\mvo\Database;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\users\User;

require_once __DIR__ . "/../bootstrap.php";

if (!isset($argv[1])) {
    echo "Usage: " . $argv[0] . " <username>\n";
    exit(1);
}

$username = $argv[1];

Database::init();

$user = User::getByUsername($username);
if ($user === null) {
    echo "User '" . $username . "' not found!\n";
    exit(1);
}

$permissions = GroupList::load()->getPermissionsForUser($user);

if ($permissions === null) {
    echo "User '" . $username . "' does not have any permissions!\n";
    exit(1);
}

foreach ($permissions as $permission) {
    echo $permission . "\n";
}