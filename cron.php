<?php
require __DIR__ . '/vendor/autoload.php';
/**
 * This script will be called periodically as a cron job.
 */

use otn\linkeddatex2\gather\GraphProcessor;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use otn\linkeddatex2\gather\ParkingHistoryFilesystem;
use GO\Scheduler;

// Scheduler setup
// https://github.com/peppeocchi/php-cron-scheduler
// If this script is called with argument "debug", it will simply acquire and write data once
if ($argc == 1) {
    $scheduler = new Scheduler();
    $scheduler->call(function() {
        acquire_data();
        sleep(30);
        acquire_data();
    })->at('* * * * *')->output(__DIR__.'/log/cronjob.log');
    $scheduler->run();
} else if ($argv[1] === "debug") {
    acquire_data();
} else if ($argv[1] === "refresh_static") {
    ParkingHistoryFilesystem::refresh_static_data();
}

/**
 * This function simply periodically saves the entire turtle file with the current ISO timestamp as filename
 * + triples for timestamp and filename of previous file
 */
// TODO FIRST group files by 15 minutes (this is +-75 KiB)
function acquire_data() {
    $out_adapter = new Local(__DIR__ . "/public/parking/out");
    $out = new Filesystem($out_adapter);
    date_default_timezone_set("Europe/Brussels");
    $graph = GraphProcessor::construct_graph();

    // Describe file timestamp and link to previous turtle file in triple
    // TODO use hydra with HTTP urls here
    // TODO this shouldn't be written to disk, only dynamic

    // write to file
    $out->write(date("c") . ".turtle", $graph->serialise("turtle"));
}
