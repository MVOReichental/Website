#! /usr/bin/env php
<?php
use src\Database;
use src\model\session\Sessions;

require_once __DIR__ . "/../bootstrap.php";

Database::init();
Sessions::gc();