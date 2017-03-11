<?php
require __DIR__ . '/../vendor/autoload.php';

// TODO add command line arguments (amount of queries, location to save file)

use \otn\linkeddatex2\gather\GraphProcessor;
use \otn\linkeddatex2\gather\FragmentedWriter;


$total_queries = 20;
date_default_timezone_set("Europe/Brussels");
$writer = new FragmentedWriter(date("Ymd"), __DIR__ . '/out', 1024); // 1 KiB for testing
$interval = 1;

for ($i = 0; $i < $total_queries; $i++) {
    print($i . "\n");
    $arr_graph = GraphProcessor::construct_graph();
    //$arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
    $parkings = GraphProcessor::get_parkings_from_graph($arr_graph);

    if ($i == 0) {
        $static_data = GraphProcessor::strip_static_data_from_parkings($parkings);
        $writer->write(serialize($static_data)); // TODO PHP serialize or JSON?
    }

    $dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);
    $writer->write(serialize($dynamic_data));
    sleep($interval);
}