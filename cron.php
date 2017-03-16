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

// TODO deploy this to testing laptop

// Scheduler setup
// https://github.com/peppeocchi/php-cron-scheduler
//$scheduler = new Scheduler();
//$scheduler->call('acquire_data')->at('* * * * *');

acquire_data();

function acquire_data() {
    $KiB = 1024; // readability
    date_default_timezone_set("Europe/Brussels");
    $writer = new DeployingWriter(__DIR__ . '/public/parking', 0.5*$KiB); // 10 KiB for testing
    $resources_adapter = new Local(__DIR__ . "/resources");
    $resources = new Filesystem($resources_adapter);
    $static_refresh_interval = 30;
    $static_data = null;

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

    $result = GraphProcessor::construct_graph($refresh_static_data);
    $arr_graph = $result["graph"];
    $static_headers = $result["static_headers"];
    $dynamic_headers = $result["dynamic_headers"]; //TODO when website uses caching headers, we can use these to regulate querying frequency

    //$arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
    $parkings = GraphProcessor::get_parkings_from_graph($arr_graph);
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
