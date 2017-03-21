<?php
require __DIR__ . '/../../vendor/autoload.php';

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

// TODO static data should never be queried! Save once, add dynamically
// TODO publish static data with cache header on separate api point (no priority)

// If no preferred content type is specified, prefer turtle
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}

$graph = null; $filename = null;
$out_adapter = new Local(__DIR__ . "/out");
$out = new Filesystem($out_adapter);

if (!isset($_GET['page']) && !isset($_GET['time'])) {
    // If no page name provided, get current file (highest timestamp)
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
    $filename = $_GET['page'];
    // TODO this should also be delegated to a Filesystem class
    if (!$out->has($filename)) {
        http_response_code(404);
        die();
    }
    $all = $out->listContents();
    $index = 0;
    $has_prev = false; $prev = null;
    $has_next = false; $next = null;
    while ($all[$index]["basename"] !== $filename) $index++;
    if ($index != 0) {
        $has_prev = true;
    }
    if ($index < count($all)-1) {
        $has_next = true;
    }
} else if (isset($_GET['time'])) {
    // If time stamp is provided, find the file containing relevant info
    // This is the latest file before the provided timestamp
    // Timestamps must be provided as: YYYY-MM-DDCEThh:mm:ss
    $all = $out->listContents();
    // TODO when direct addressing is used, change this to a separate file_search() method
    $index = 0;
    $current_file = $all[$index];
    $current_timestamp = strtotime(substr($current_file["basename"], 0, 21));
    // TODO this breaks if timestamps in the future are queried. Fix it in filesystem class
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