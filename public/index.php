<?php
require __DIR__ . '/../vendor/autoload.php';

$graph = null;

\otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/", $graph);
\otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkingsstatus", $graph);
\otn\linkeddatex2\Metadata::addToGraph($graph);
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}
\otn\linkeddatex2\View::view($_SERVER['HTTP_ACCEPT'],$graph);