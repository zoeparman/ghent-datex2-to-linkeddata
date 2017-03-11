<?php
require __DIR__ . '/../vendor/autoload.php';

use \otn\linkeddatex2\gather\ParkingDeserializer;

$deserializer = new ParkingDeserializer(1, __DIR__ . '/out');
$parking_data = $deserializer->aggregate_for_base(date("Ymd"));
var_dump($parking_data);