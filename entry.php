<?php

use ReverseGpx\ReverseGpx;

require_once('ReverseGpx.php');

if (isset($argv[1])) {
    $path = $argv[1];

    $reverser = new ReverseGpx($path);

    $reverser->reverse();
} else {
    die('Cannot find gpx to reverse' . PHP_EOL);
}
