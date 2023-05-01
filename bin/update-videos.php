#! /usr/bin/env php
<?php
use src\model\videos\VideoList;

require_once __DIR__ . "/../bootstrap.php";

VideoList::loadFromYouTubeAPI()->save();