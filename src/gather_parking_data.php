<?php
require __DIR__ . '/../vendor/autoload.php';

// TODO add command line arguments (amount of queries, location to save file)

use \otn\linkeddatex2\gather\GraphProcessor;
use \otn\linkeddatex2\gather\FragmentedWriter;

$start_time = time();
$duration = 60*60*4; // 4 hours of recording
$KiB = 1024; // readability

date_default_timezone_set("Europe/Brussels");
$writer = new FragmentedWriter(date("Ymd"), __DIR__ . '/out', 10*$KiB); // 10 KiB for testing
$writer->clear_directory(); // Start over from 0 (for testing)
$interval = 30; // TODO use caching headers when available
$static_data_written = false;

while (time() - $start_time < $duration) {
    $arr_graph = GraphProcessor::construct_graph();
    //$arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
    $parkings = GraphProcessor::get_parkings_from_graph($arr_graph);

    if (!$static_data_written) {
        $static_data = GraphProcessor::strip_static_data_from_parkings($parkings);
        $writer->write(serialize($static_data)); // TODO PHP serialize or JSON?
        $static_data_written = true;
    }

    $dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);
    $writer->write(serialize($dynamic_data));
    print($dynamic_data["time"] . "\n");
    sleep($interval);
}