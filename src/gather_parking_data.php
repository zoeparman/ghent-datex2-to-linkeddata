<?php
require __DIR__ . '/../vendor/autoload.php';

// TODO add command line arguments (amount of queries, location to save file)

$total_queries = 10;
for ($i = 0; $i < $total_queries; $i++) {
    $graph = new \EasyRdf_Graph(); // Initializing here allows PHPStorm to infer methods and properties

    // Map static info about parkings in Ghent to the graph
    // (Name, lat, long, ID, number of spaces, opening times)
    \otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/", $graph);

    // Map dynamic info about parkings in Ghent to the graph
    // (ID, occupancy, availability status, opening status)
    \otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkingsstatus", $graph);

    // Convert the graph to a PHP RDF array
    $rdf_graph = $graph->toRdfPhp();

    // Define prefixes
    $datex = "http://vocab.datex.org/terms#";
    $owl = "http://www.w3.org/2002/07/owl#";

    // Get all parkings from PHP RDF array
    // TODO this is ugly, there is probably a better way
    $parking_prefix = "https://stad.gent/id/parking/P";
    $parking_nums = array(1,2,4,7,8,10);
    foreach($parking_nums as $p_num) {
        $parking = $rdf_graph[$parking_prefix . $p_num];
        $desc = $parking["http://purl.org/dc/terms/description"][0]['value'];
        print($desc . ":\n");
        print("\t" . "Total number of spaces: " . $parking[$datex . "parkingNumberOfSpaces"][0]['value'] . "\n");
        print("\t" . "Number of vacant spaces: " . $parking[$datex . "parkingNumberOfVacantSpaces"][0]['value'] . "\n");
    }


    // Save to file
    // TODO

    // Wait for next query (this is 1 for testing purposes, should be cache time)
    sleep(1);
}