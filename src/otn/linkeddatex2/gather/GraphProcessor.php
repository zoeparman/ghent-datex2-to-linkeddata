<?php

namespace otn\linkeddatex2\gather;

use otn\linkeddatex2\GhentToRDF;

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

    public static function construct_graph() {
        $graph = new \EasyRdf_Graph(); // Initializing here allows PHPStorm to infer methods and properties

        // Map static info about parkings in Ghent to the graph
        // (Name, lat, long, ID, number of spaces, opening times)
        GhentToRDF::map(self::$urls["static_data"], $graph);

        // Map dynamic info about parkings in Ghent to the graph
        // (ID, occupancy, availability status, opening status)
        GhentToRDF::map(self::$urls["dynamic_data"], $graph);

        // Convert the graph to a PHP RDF array
        $arr_graph = $graph->toRdfPhp();

        return $arr_graph;
    }

    public static function construct_stub_graph() {
        $result = array();

        foreach(self::$parking_nums as $p_num) {
            $result[self::$prefixes["parking"] . $p_num] = array(
                self::$urls["description"] => array(array('value' => 'TEST_PARKING_' . $p_num)),
                self::$prefixes["datex"] . self::$prefixes["total_spaces"] => array(array('value' => $p_num*100)),
                self::$prefixes["datex"] . self::$prefixes["vacant_spaces"] => array(array('value' => $p_num*20)),
            );
        }

        return $result;
    }

    public static function get_parkings_from_graph($arr_graph) {
        $result = array();

        foreach(self::$parking_nums as $p_num) {
            $result[$p_num] = $arr_graph[self::$prefixes["parking"] . $p_num];
        }

        return $result;
    }

    public static function strip_static_data_from_parkings($parkings) {
        $result = array();

        foreach($parkings as $p_num => $parking) {
            $desc = $parking[self::$urls["description"]][0]['value'];
            $total = $parking[self::$prefixes["datex"] . self::$prefixes["total_spaces"]][0]['value'];
            $result["parking_" . $p_num] = array(
                "description" => $desc,
                "total_spaces" => $total
            );
        }

        return $result;
    }

    public static function strip_dynamic_data_from_parkings($parkings) {
        $result = array("time" => date("Gis"));

        foreach($parkings as $p_num => $parking) {
            $vacant = $parking[self::$prefixes["datex"] . self::$prefixes["vacant_spaces"]][0]['value'];
            $result["parking_" . $p_num] = $vacant;
        }

        return $result;
    }
}