<?php
require __DIR__ . '/../../vendor/autoload.php';

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

// If no preferred content type is specified, prefer turtle
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}

// Get current file (highest timestamp) and read as graph
$out_adapter = new Local(__DIR__ . "/out");
$out = new Filesystem($out_adapter);
$all = $out->listContents();
$max_timestamp = 0; $latest_file = null;
foreach ($all as $index => $file) {
    if ($file["timestamp"] > $max_timestamp) {
        $max_timestamp = $file["timestamp"];
        $latest_file = $file;
    }
}
$latest_file_contents = $out->read($latest_file["basename"]);
$graph = new EasyRdf_Graph();
$graph->parse($latest_file_contents, "turtle");

// Set HTTP headers and serialize graph
\otn\linkeddatex2\View::view($_SERVER['HTTP_ACCEPT'],$graph);