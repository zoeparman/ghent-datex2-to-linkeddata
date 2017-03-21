<?php
require __DIR__ . '/../../vendor/autoload.php';

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

// Usage: record.php -> get latest record. Contains a timestamp of the previous file
//        record.php?timestamp=YYYY-MM-DDCEThh:mm:ss -> get record with given timestamp

// If no preferred content type is specified, prefer turtle
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}

$graph = null; $filename = null;
$out_adapter = new Local(__DIR__ . "/out");
$out = new Filesystem($out_adapter);

if (!isset($_GET['timestamp'])) {
    // If no timestamp provided, get current file (highest timestamp)
    $all = $out->listContents();
    $max_timestamp = 0; $latest_file = null;
    foreach ($all as $index => $file) {
        if ($file["timestamp"] > $max_timestamp) {
            $max_timestamp = $file["timestamp"];
            $latest_file = $file;
        }
    }
    $filename = $latest_file["basename"];
} else {
    $filename = $_GET["timestamp"] . ".turtle";
    if (!$out->has($filename)) {
        die("Invalid timestamp provided");
    }
}

$latest_file_contents = $out->read($filename);
$graph = new EasyRdf_Graph();
$graph->parse($latest_file_contents, "turtle");

// Set HTTP headers and serialize graph
\otn\linkeddatex2\View::view($_SERVER['HTTP_ACCEPT'],$graph);