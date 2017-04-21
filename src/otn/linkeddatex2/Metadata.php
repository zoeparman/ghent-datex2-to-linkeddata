<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;

use \Dotenv;

Class Metadata
{
    private static function addTriple(&$graph, $subject, $predicate, $object) {
        array_push($graph, [
            'graph' => '#Metadata',
            'subject' => $subject,
            'predicate' => $predicate,
            'object' => $object
        ]);
    }

    public static function add_counts_to_multigraph(&$multigraph, $graph_name) {
        $graph_counts = array();
        $triples = 0;
        foreach ($multigraph as $quad) {
            if ($quad['graph'] !== "") {
                if (!isset($graph_counts[$quad['graph']])) {
                    $graph_counts[$quad['graph']] = 1;
                } else {
                    $graph_counts[$quad['graph']]++;
                }
            }
            $triples++;
        }
        foreach ($graph_counts as $graph => $count) {
            array_push($multigraph, [
                // TODO what is the subject here?
                'graph' => $graph,
                'subject' => $graph,
                'predicate' => 'void:triples',
                'object' => $graph_counts[$graph] + 1
            ]);
            $triples++;
        }
        array_push($multigraph, [
            // TODO what is the subject here?
            'subject' => $graph_name,
            'predicate' => 'void:triples',
            'object' => $triples + 1
        ]);
    }

    public static function get() {
        $dotenv = new Dotenv\Dotenv(__DIR__ . "/../../../");
        $dotenv->load();
        $base_url = $_ENV["BASE_URL"];

        $result = array();
        $dataset = $base_url . "#dataset";
        $document = $base_url;
        $search = $base_url . "#search";
        $mappingS = $base_url . "#mappingS";
        $mappingP = $base_url . "#mappingP";
        $mappingO = $base_url . "#mapping0";

        $doc_triples = [
            ['rdfs:label', '"Dynamic parking data in Ghent"'],
            ['rdfs:comment', '"This document is a mapping from the Datex2 by Pieter Colpaert as part of the Open Transport Net project"'],
            ['foaf:homepage', 'https://github.com/opentransportnet/ghent-datex2-to-linkeddata'],
            ['cc:license', "https://data.stad.gent/algemene-licentie"]];
        foreach ($doc_triples as $triple) {
            self::addTriple($result, $document, $triple[0], $triple[1]);
        }

        self::addTriple($result, $dataset, "hydra:search", $search);
        self::addTriple($result, $mappingS, "hydra:variable", '"s"');
        self::addTriple($result, $mappingP, "hydra:variable", '"p"');
        self::addTriple($result, $mappingO, "hydra:variable", '"o"');
        self::addTriple($result, $mappingS, "hydra:property", '"subject"');
        self::addTriple($result, $mappingP, "hydra:property", '"property"');
        self::addTriple($result, $mappingO, "hydra:property", '"object"');
        self::addTriple($result, $search, "hydra:template", $base_url);
        self::addTriple($result, $search, "hydra:mapping", $mappingS);
        self::addTriple($result, $search, "hydra:mapping", $mappingP);
        self::addTriple($result, $search, "hydra:mapping", $mappingO);

        return $result;
    }
}
