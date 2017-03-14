<?php
require __DIR__ . '/vendor/autoload.php';
/**
 * This script will be called periodically as a cron job.
 */

use otn\linkeddatex2\gather\DeployingWriter;
use otn\linkeddatex2\gather\GraphProcessor;

// INITIALIZATION
$KiB = 1024; // readability
date_default_timezone_set("Europe/Brussels");
$basename = date("Ymd");
$writer = new DeployingWriter(__DIR__ . '/public/parking', 0.5*$KiB); // 10 KiB for testing

// GRAPH CONSTRUCTION AND DATA STRIPPING
// TODO STATIC DATA?
// TODO CACHE HEADERS?
//$arr_graph = GraphProcessor::construct_graph();
$arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
$parkings = GraphProcessor::get_parkings_from_graph($arr_graph);
$dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);
$writer->write(json_encode($dynamic_data));
