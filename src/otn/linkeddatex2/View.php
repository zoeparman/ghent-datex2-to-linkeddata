<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;

use pietercolpaert\hardf\TriGWriter;

Class View
{
    private static function headers($acceptHeader) {
        // Content negotiation using vendor/willdurand/negotiation
        $negotiator = new \Negotiation\Negotiator();
        $priorities = array('text/turtle','application/rdf+xml');
        $mediaType = $negotiator->getBest($acceptHeader, $priorities);
        $value = $mediaType->getValue();
        header("Content-type: $value");

        //Max age is 1/2 minute for caches
        header("Cache-Control: max-age=30");

        //Allow Cross Origin Resource Sharing
        header("Access-Control-Allow-Origin: *");

        //As we have content negotiation on this document, don’t cache different representations on one URL hash key
        header("Vary: accept");
        return $value;
    }

    public static function view($acceptHeader, $graph){
        $value = self::headers($acceptHeader);
        $writer = new TriGWriter(["format" => $value]);
        $metadata = Metadata::get();
        foreach ($metadata as $quad) {
            array_push($graph, $quad);
        }
        Metadata::add_counts_to_multigraph($graph);
        $writer->addPrefixes(GhentToRDF::getPrefixes());
        $writer->addTriples($graph);
        echo $writer->end();
    }
}