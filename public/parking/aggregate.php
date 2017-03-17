<?php
require __DIR__ . '/../../vendor/autoload.php';

$from = $_GET["from"];
$to = $_GET["to"];

date_default_timezone_set("Europe/Brussels");

if (!isset($from)) {
    die("Provide a from timestamp (YYYY-MM-DD+hh:mm:ss)");
}
if (!isset($to)) {
    $to = date("Y-m-d H:i:s");
}

$aggregator = new \otn\linkeddatex2\gather\Aggregator(__DIR__ . "/json");
$data = $aggregator->aggregate($from, $to);
echo json_encode($data);