#! /usr/bin/env php
<?php

use src\mail\Queue;

require_once __DIR__ . "/../bootstrap.php";

Queue::process();