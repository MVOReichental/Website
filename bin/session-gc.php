#! /usr/bin/env php
<?php
use de\mvo\Database;
use de\mvo\model\session\Sessions;

require_once __DIR__ . "/../bootstrap.php";

Database::init();
Sessions::gc();