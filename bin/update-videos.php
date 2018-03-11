#! /usr/bin/env php
<?php
use de\mvo\model\videos\VideoList;

require_once __DIR__ . "/../bootstrap.php";

VideoList::loadFromYouTubeAPI()->save();