<?php
use de\mvo\Database;
use de\mvo\model\permissions\GroupList;

require_once __DIR__ . "/../bootstrap.php";

if (!isset($argv[1])) {
    echo "Usage: " . $argv[0] . " <json file>\n";
    exit(1);
}

$json = json_decode(file_get_contents($argv[1]));

if ($json === null) {
    echo "Unable to parse JSON!\n";
    exit(1);
}

Database::init();

GroupList::loadFromArray($json)->save();