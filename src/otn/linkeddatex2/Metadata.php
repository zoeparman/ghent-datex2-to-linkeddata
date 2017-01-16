<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;

Class Metadata
{
    public static function addToGraph($graph){
        \EasyRdf_Namespace::set("hydra","http://www.w3.org/ns/hydra/core#");
        //This is a template for the Triple Pattern Fragments specification (https://www.hydra-cg.com/spec/latest/triple-pattern-fragments/), which will allow you to use this document as a queryable resource in the Linked Data Fragments client (http://client.linkeddatafragments.org/)
        $dataset = $graph->resource("http://localhost:1234/#dataset");
        $document = $graph->resource("http://localhost:1234/");
        $document->set('void:count', "200");
        
        $search = $graph->resource("http://localhost:1234/#search");
        $dataset->add("hydra:search",$search);
        $search->set("hydra:template", "http://localhost:1234/");
        $mappingS = $graph->resource("http://localhost:1234/#mappingS");
        $mappingS->set("hydra:variable","s");
        $mappingS->add("hydra:property",$graph->resource("rdf:subject"));
        $mappingP = $graph->resource("http://localhost:1234/#mappingP");
        $mappingP->set("hydra:variable","p");
        $mappingP->add("hydra:property",$graph->resource("rdf:predicate"));
        $mappingO = $graph->resource("http://localhost:1234/#mappingO");
        $mappingO->set("hydra:variable","o");
        $mappingO->add("hydra:property",$graph->resource("rdf:object"));
        $search->add("hydra:mapping",$mappingS);
        $search->add("hydra:mapping",$mappingP);
        $search->add("hydra:mapping",$mappingO);
        //add a license for legal interoperability
        $document = $graph->resource("http://linked.open.gent/parking");
        $document->set("rdfs:label", "Dynamic parking data in Ghent");
        $document->set("rdfs:comment", "This document is a mapping from the Datex2 by Pieter Colpaert as part of the Open Transport Net project");
        $document->add("foaf:homepage", $graph->resource("https://github.com/opentransportnet/ghent-datex2-to-linkeddata"));
        $document->add("cc:license",$graph->resource("https://data.stad.gent/algemene-licentie"));
    }
}