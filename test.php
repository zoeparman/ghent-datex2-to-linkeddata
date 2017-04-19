<?php

require __DIR__ . '/vendor/autoload.php';

use otn\linkeddatex2\gather\GraphProcessor;
use \Dotenv\Dotenv;

$urls = array(
    "static_data" => "http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/",
    "dynamic_data" => "http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkingsstatus"
);

$time = substr(date("c"), 0, 19);
$dotenv = new Dotenv(__DIR__);
$dotenv->load();
$base_url = $_ENV["BASE_URL"] . "?time=";
//$graph = new \EasyRdf_Graph($base_url . $time); // Initializing here allows PHPStorm to infer methods and properties

// Map real-time info about parkings in Ghent to the graph
// (ID, occupancy, availability status, opening status)
// Slowly changing info (name, description, etc) is saved once and added upon request
//GhentToRDF::map($urls["dynamic_data"], $graph);
$fs = new \otn\linkeddatex2\gather\ParkingHistoryFilesystem("public/parking/out", "resources");
$graph = $fs->get_graphs_from_file_with_links("2017-03-28T12:35:00.turtle");
var_dump($graph);