<?php
require __DIR__ . '/../vendor/autoload.php';

// TODO add command line arguments (amount of queries, location to save file)

use \otn\linkeddatex2\graph\GraphProcessor;
use \League\Flysystem\Filesystem;
use \League\Flysystem\Adapter\Local;

$total_queries = 10;

$adapter = new Local(__DIR__ . '/out');
$filesystem = new Filesystem($adapter);
if ($filesystem->has('out.txt')) {
    $filesystem->delete('out.txt');
}
for ($i = 0; $i < $total_queries; $i++) {
    print($i);
    //$arr_graph = GraphProcessor::construct_graph();
    $arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
    $parkings = GraphProcessor::get_parkings_from_graph($arr_graph);

    if ($i == 0) {
        $static_data = GraphProcessor::strip_static_data_from_parkings($parkings);
        append_to_file($filesystem, 'out.txt', serialize($static_data)); // TODO PHP serialize or JSON?
    }

    $dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);
    append_to_file($filesystem, 'out.txt', serialize($dynamic_data));
    sleep(1);
}

// TODO can Flysystem append to files?
function append_to_file(Filesystem $filesystem, $filename, $content) {
    $cur = "";
    if ($filesystem->has($filename)) {
        $cur = $filesystem->read($filename);
    }
    $filesystem->put($filename, $cur . '\n' . $content);
}