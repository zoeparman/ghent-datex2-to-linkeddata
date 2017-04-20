<?php

namespace otn\linkeddatex2\gather;

use otn\linkeddatex2\GhentToRDF;
use \Dotenv;

class GraphProcessor
{
    public static function construct_graph() {
        $time = substr(date("c"), 0, 19);
        $dotenv = new Dotenv\Dotenv(__DIR__ . "/../../../../");
        $dotenv->load();
        $base_url = $_ENV["BASE_URL"] . "?time=";
        $graph = GhentToRDF::get(GhentToRDF::DYNAMIC);

        $graph = self::remove_triples_with($graph, ['predicate'], ['datex:parkingSiteStatus']);
        $graph = self::remove_triples_with($graph, ['predicate'], ['datex:parkingSiteOpeningStatus']);
        $graph = self::remove_triples_with($graph, ['predicate'], ['owl:sameAs']);

        foreach ($graph["triples"] as $triple) {
            $triple['graph'] = $base_url . $time;
        }

        return $graph;
    }

    /** Remove triples for which every given component has the given respective value
     * eg:
     * $components = ['resource', 'predicate'];
     * $values = ['https://stad.gent/id/parking/P7', 'owl:sameAs']
     * removes all triples of resource https://stad.gent/id/parking/P7 with predicate owl:sameAs
    */
    private static function remove_triples_with($graph, $components, $values) {
        $result = [
            "prefixes" => $graph["prefixes"],
            "triples" => []
        ];
        foreach ($graph["triples"] as $triple) {
            $remove = true;
            foreach ($components as $index => $component) {
                if ($triple[$component] !== $values[$index]) {
                    $remove = false;
                }
            }
            if (!$remove) {
                array_push($result["triples"], $triple);
            }
        }
        return $result;
    }

    public static function get_static_data() {
        return GhentToRDF::get(GhentToRDF::STATIC);
    }
}