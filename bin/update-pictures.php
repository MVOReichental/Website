#! /usr/bin/env php
<?php
use de\mvo\model\pictures\YearList;

require_once __DIR__ . "/../bootstrap.php";

YearList::loadFromJson()->save();