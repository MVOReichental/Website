#! /usr/bin/env php
<?php
use src\Database;
use src\model\permissions\GroupList;

require_once __DIR__ . "/../bootstrap.php";

Database::init();

$json = GroupList::load();

echo json_encode($json, JSON_PRETTY_PRINT);