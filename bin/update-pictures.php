#! /usr/bin/env php
<?php
use src\model\pictures\YearList;

require_once __DIR__ . "/../bootstrap.php";

YearList::loadFromJson()->save();