<?php
require __DIR__ . '/../vendor/autoload.php';

// TODO add command line arguments (amount of queries, location to save file)

use \otn\linkeddatex2\graph\GraphProcessor;

$total_queries = 10;

for ($i = 0; $i < $total_queries; $i++) {
    //$arr_graph = GraphProcessor::construct_graph();
    $arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down

    $parkings = GraphProcessor::get_parkings_from_graph($arr_graph);

    $static_data = GraphProcessor::strip_static_data_from_parkings($parkings);

    $dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);

    // Save to file
    // TODO
    var_dump($dynamic_data);

    // Wait for next query (this is 1 for testing purposes, should be cache time)
    sleep(1);
}