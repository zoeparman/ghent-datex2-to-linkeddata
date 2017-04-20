<?php

namespace otn\linkeddatex2\gather;

use otn\linkeddatex2\GhentToRDF;
use \Dotenv;

/**
 * Class GraphProcessor
 * @package otn\linkeddatex2\gather
 * Contains static methods for constructing the RDF graph and stripping data
 */
class GraphProcessor
{
    private static $prefixes = array(
        "datex" => "http://vocab.datex.org/terms#",
        "vacant_spaces" => "parkingNumberOfVacantSpaces",
        "total_spaces" => "parkingNumberOfSpaces",
        "parking" => "https://stad.gent/id/parking/P"
    );

    private static $urls = array(
        "static_data" => "http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/",
        "dynamic_data" => "http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkingsstatus",
        "description" => "http://purl.org/dc/terms/description"
    );

    private static $parking_nums = array(1,2,4,7,8,10);

    /**
     * Construct the graph using data from two websites.
     * @return \EasyRdf_Graph $graph: RDF graph containing parking info
     */
    public static function construct_graph() {
        $time = substr(date("c"), 0, 19);
        $dotenv = new Dotenv\Dotenv(__DIR__ . "/../../../../");
        $dotenv->load();
        $base_url = $_ENV["BASE_URL"] . "?time=";
        $graph = new \EasyRdf_Graph($base_url . $time); // Initializing here allows PHPStorm to infer methods and properties

        // Map real-time info about parkings in Ghent to the graph
        // (ID, occupancy, availability status, opening status)
        // Slowly changing info (name, description, etc) is saved once and added upon request
        GhentToRDF::map(self::$urls["dynamic_data"], $graph);

        // Remove unnecessary information
        foreach ($graph->resources() as $resource) {
            $graph->deleteSingleProperty($resource, "datex:parkingSiteStatus");
            $graph->deleteSingleProperty($resource, "datex:parkingSiteOpeningStatus");
            $graph->deleteSingleProperty($resource, "owl:sameAs");
        }

        return $graph;
    }

    /**
     * Construct a stub graph for offline testing.
     * @return array: A PHP array containing a stub for the parking graph.
     */
    public static function construct_stub_graph() {
        $result = array();

        foreach(self::$parking_nums as $p_num) {
            $result[self::$prefixes["parking"] . $p_num] = array(
                self::$urls["description"] => array(array('value' => 'TEST_PARKING_' . $p_num)),
                self::$prefixes["datex"] . self::$prefixes["total_spaces"] => array(array('value' => (string)($p_num*100))),
                self::$prefixes["datex"] . self::$prefixes["vacant_spaces"] => array(array('value' => (string)($p_num*20))),
            );
        }

        return $result;
    }

    public static function get_static_data() {
        $graph = new \EasyRdf_Graph();
        GhentToRDF::map(self::$urls["static_data"], $graph);
        return $graph;
    }
}