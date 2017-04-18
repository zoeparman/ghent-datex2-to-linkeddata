<?php

namespace otn\linkeddatex2\gather;

use otn\linkeddatex2\GhentToRDF;
use \Dotenv;

class GraphProcessor
{
    public static function construct_graph() {
        //TODO hardf here
        $time = substr(date("c"), 0, 19);
        $dotenv = new Dotenv\Dotenv(__DIR__ . "/../../../../");
        $dotenv->load();
        $base_url = $_ENV["BASE_URL"] . "?time=";
        $graph = new \EasyRdf_Graph($base_url . $time); // Initializing here allows PHPStorm to infer methods and properties

        // Map real-time info about parkings in Ghent to the graph
        // (ID, occupancy, availability status, opening status)
        // Slowly changing info (name, description, etc) is saved once and added upon request

        //GhentToRDF::map(self::$urls["dynamic_data"], $graph);
        $graph = GhentToRDF::get(GhentToRDF::DYNAMIC);

        // Remove unnecessary information
        foreach ($graph->resources() as $resource) {
            $graph->deleteSingleProperty($resource, "datex:parkingSiteStatus");
            $graph->deleteSingleProperty($resource, "datex:parkingSiteOpeningStatus");
            $graph->deleteSingleProperty($resource, "owl:sameAs");
        }

        return $graph;
    }

    public static function get_static_data() {
        return GhentToRDF::get(GhentToRDF::STATIC);
    }
}