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
$graph = GraphProcessor::construct_graph();
var_dump($graph);