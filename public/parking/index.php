<?php
require __DIR__ . '/../../vendor/autoload.php';

// TODO publish static data with cache header on separate api point (no priority)


// If no preferred content type is specified, prefer turtle
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}

$graph = null; $filename = null;

$fs = new \otn\linkeddatex2\gather\ParkingHistoryFilesystem(__DIR__ . "/out", __DIR__ . "../../resources");

if (!isset($_GET['page']) && !isset($_GET['time'])) {
    $filename = $fs->get_last_page();
} else if (isset($_GET['page'])) {
    // If page name is provided, it must be exact
    $filename = $_GET['page'];
    if (!$fs->has_file($filename)) {
        http_response_code(404);
        die();
    }
} else if (isset($_GET['time'])) {
    // If timestamp is provided, find latest file before timestamp
    $filename = $fs->get_closest_page_for_timestamp(strtotime($_GET['time']));
    if (!$filename) {
        http_response_code(404);
        die();
    }
}

if (!isset($_GET['page'])) {
    header('Location: http://' . $_SERVER["SERVER_NAME"] . '/parking?page=' . $filename);
} else {
    // TODO content negotiation using views, once graphs are saved properly
    // TODO add dynamically: static data, link to previous, link to next (hydra) (use get_prev and get_next from PHFS)
    echo $fs->get_file_contents($filename);
    /*$contents = $fs->read($filename);
    $graph = new EasyRdf_Graph();
    $graph->parse($contents, "turtle");

    // Add extra triples

    // Set HTTP headers and serialize graph
    \otn\linkeddatex2\View::view($_SERVER['HTTP_ACCEPT'],$graph);*/
}