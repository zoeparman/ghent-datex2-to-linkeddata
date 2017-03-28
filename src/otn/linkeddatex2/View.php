<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;


use otn\linkeddatex2\gather\TrigSerializer;

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
        echo $graph->serialise($value);
    }

    // TODO no content negotiation because TriG is now only supported format?
    public static function view_quads($graphs) {
        header("Cache-Control: max-age=30");
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: trig");
        $meta = self::get_metadata_graph($graphs);
        Metadata::addToGraph($meta);
        $serializer = new TrigSerializer();
        echo $serializer->serialize($graphs);
    }

    private static function get_metadata_graph(&$graphs) {
        foreach($graphs as $graph) {
            if ($graph->getUri() === "Metadata") {
                return $graph;
            }
        }
        $metadata = new \EasyRdf_Graph("Metadata");
        array_push($graphs, $metadata);
        return $metadata;
    }
}