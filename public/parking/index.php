<?php
require __DIR__ . '/../../vendor/autoload.php';

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

// If no preferred content type is specified, prefer turtle
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}

$graph = null; $filename = null;
$out_adapter = new Local(__DIR__ . "/out");
$out = new Filesystem($out_adapter);

if (!isset($_GET['page']) && !isset($_GET['time'])) {
    // If no page name provided, get current file (highest timestamp)
    // TODO this will become too large when the server has been running for a while. Create directory tree: year-month-day-hour
    $all = $out->listContents();
    $max_timestamp = 0; $latest_file = null;
    foreach ($all as $index => $file) {
        if ($file["timestamp"] > $max_timestamp) {
            $max_timestamp = $file["timestamp"];
            $latest_file = $file;
        }
    }
    $filename = $latest_file["basename"];
} else if (isset($_GET['page'])) {
    // If page name is provided, it must be exact
    $filename = $_GET["page"];
    if (!$out->has($filename)) {
        http_response_code(404);
        die();
    }
} else if (isset($_GET['time'])) {
    // If time stamp is provided, find the file containing relevant info
    // This is the latest file before the provided timestamp
    // Timestamps must be provided as: YYYY-MM-DDCEThh:mm:ss
    $all = $out->listContents();
    // TODO when directory tree is used, change this to a separate file_search() method
    $index = 0;
    $current_file = $all[$index];
    $current_timestamp = strtotime(substr($current_file["basename"], 0, 21));
    // TODO this breaks if timestamps in the future are queried. Fix it after directory tree is implemented
    while ($current_timestamp < strtotime($_GET['time'])) {
        $index++;
        $current_file = $all[$index];
        $current_timestamp = strtotime(substr($current_file["basename"], 0, 21));
    }
    $current_file = $all[$index - 1]; // We have passed the timestamp exactly once
    $filename = $current_file["basename"];
}

if (!isset($_GET['page'])) {
    header('Location: http://' . $_SERVER["SERVER_NAME"] . '/parking?page=' . $filename);
} else {
    $latest_file_contents = $out->read($filename);
    $graph = new EasyRdf_Graph();
    $graph->parse($latest_file_contents, "turtle");

    // Set HTTP headers and serialize graph
    \otn\linkeddatex2\View::view($_SERVER['HTTP_ACCEPT'],$graph);
}