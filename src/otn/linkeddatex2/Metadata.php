<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;

// TODO url in config file

Class Metadata
{
    private static function addTriple(&$graph, $subject, $predicate, $object) {
        array_push($graph, [
            'graph' => 'Metadata',
            'subject' => $subject,
            'predicate' => $predicate,
            'object' => $object
        ]);
    }

    //TODO prefixes
    public static function get() {
        $result = array();
        //$result["prefixes"]["hydra"] = "http://www.w3.org/ns/hydra/core#";
        $dataset = "http://linked.open.gent/parking/#dataset";
        $document = "http://linked.open.gent/parking/";
        $search = "http://linked.open.gent/parking/#search";
        $mappingS = "http://linked.open.gent/parking/#mappingS";
        $mappingP = "http://linked.open.gent/parking/#mappingP";
        $mappingO = "http://linked.open.gent/parking/#mapping0";

        $doc_triples = [
            ['void:triples', '"200"'],
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
        self::addTriple($result, $search, "hydra:template", "http://linked.open.gent/parking/");
        self::addTriple($result, $search, "hydra:mapping", $mappingS);
        self::addTriple($result, $search, "hydra:mapping", $mappingP);
        self::addTriple($result, $search, "hydra:mapping", $mappingO);

        return $result;
    }
}
