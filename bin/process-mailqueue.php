#! /usr/bin/env php
<?php

use de\mvo\mail\Queue;

require_once __DIR__ . "/../bootstrap.php";

Queue::process();