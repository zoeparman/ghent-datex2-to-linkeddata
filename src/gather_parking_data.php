<?php
require_once __DIR__ . '../vendor/autoload.php';


// TODO add command line arguments (for duration etc)

$total_queries = 10;
for ($i = 0; $i < $total_queries; $i++) {
    $graph = null;
    // Map static info about parkings in Ghent to the graph
    // (Name, lat, long, ID, number of spaces, opening times)
    \otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/", $graph);

    // Map dynamic info about parkings in Ghent to the graph
    // (ID, occupancy, availability status, opening status)
    \otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkingsstatus", $graph);

    // Serialize the essential information
    // TODO

    // Save to file
    // TODO

    // Wait for next query (this is 1 for testing purposes, should be cache time)
    sleep(1);
}