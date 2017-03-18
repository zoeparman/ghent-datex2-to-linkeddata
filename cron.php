<?php
require __DIR__ . '/vendor/autoload.php';
/**
 * This script will be called periodically as a cron job.
 */

use otn\linkeddatex2\gather\DeployingWriter;
use otn\linkeddatex2\gather\GraphProcessor;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use GO\Scheduler;

// Scheduler setup
// https://github.com/peppeocchi/php-cron-scheduler
// If this script is called with argument "debug", it will simply acquire and write data once
if ($argc == 0) {
    $scheduler = new Scheduler();
    $scheduler->call(function() {
        acquire_data();
        sleep(30);
        acquire_data();
    })->at('* * * * *')->output(__DIR__.'/log/cronjob.log');
    $scheduler->run();
} else if ($argv[1] === "debug") {
    acquire_data();
}

/**
 * This function simply periodically saves the entire turtle file with the current ISO timestamp as filename
 */
function acquire_data() {
    $out_adapter = new Local(__DIR__ . "/public/parking/out");
    $out = new Filesystem($out_adapter);
    date_default_timezone_set("Europe/Brussels");
    $result = GraphProcessor::construct_graph(true);
    $graph = $result["graph"]; // also has static_headers and dynamic_headers

    // Get name of last file
    $out_adapter = new Local(__DIR__ . "/public/parking/out");
    $out = new Filesystem($out_adapter);
    $all = $out->listContents();
    $max_timestamp = 0; $latest_filename = null;
    foreach ($all as $index => $file) {
        if ($file["timestamp"] > $max_timestamp) {
            $max_timestamp = $file["timestamp"];
            $latest_filename = $file["filename"];
        }
    }

    // Describe file timestamp and link to previous turtle file in triple
    // TODO use timestamp link as argument for php access point?
    \EasyRdf_Namespace::set("purl", "http://purl.org/dc/terms/");
    \EasyRdf_Namespace::set("search", "http://vocab.deri.ie/search");
    $resource = $graph->resource("http://linked.open.gent/parking#current");
    $resource->add("purl:created", date("Y-m-dTH:i:s"));
    $resource->add("search:previous", $latest_filename);

    // write to file
    $out->write(date("Y-m-dTH:i:s") . ".turtle", $graph->serialise("turtle"));
}

/**
 * Old version of data acquire function, using json files (deprecated?)
 */
function acquire_data_old() {
    // Variables
    $KiB = 1024; // readability
    $minute = 60; // readability
    date_default_timezone_set("Europe/Brussels");
    $writer = new DeployingWriter(__DIR__ . '/public/parking/out', 0.5*$KiB); // 10 KiB for testing
    $resources_adapter = new Local(__DIR__ . "/resources");
    $resources = new Filesystem($resources_adapter);
    $static_refresh_interval = 10*$minute;
    $static_data = null;

    // Check if necessary to query static data
    $refresh_static_data = true;
    if ($resources->has("static_data")) {
        $static_str = $resources->read("static_data");
        $static_data_resource = unserialize($static_str);
        $diff = time() - $static_data_resource["created_at"];
        if ($diff < $static_refresh_interval) {
            $refresh_static_data = false;
            $static_data = $static_data_resource["data"];
        }
    }

    // Construct graph and get headers
    $result = GraphProcessor::construct_graph($refresh_static_data);
    //$arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
    $graph = $result["graph"];
    $static_headers = null;
    if (isset($result["static_headers"])) {
        $static_headers = $result["static_headers"];
    }
    $dynamic_headers = $result["dynamic_headers"]; //TODO when website uses caching headers, we can use these to regulate querying frequency

    $parkings = GraphProcessor::get_parkings_from_graph($graph);
    if ($refresh_static_data) {
        $static_data = GraphProcessor::strip_static_data_from_parkings($parkings);
        $resources->update("static_data", serialize(array(
            "created_at" => time(),
            "data" => $static_data
        )));
    }
    $writer->set_deployment_metadata($static_data);
    $dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);
    $writer->write(json_encode($dynamic_data));
}
