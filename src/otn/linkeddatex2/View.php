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

    public static function view_quads($acceptHeader, $graphs) {
        $value = self::headers($acceptHeader);
        $serializer = new TrigSerializer();
        echo $serializer->serialize($graphs);
    }
}