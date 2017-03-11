<?php

namespace otn\linkeddatex2\gather;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

/**
 * Class ParkingDeserializer
 * @package otn\linkeddatex2\gather
 * A ParkingDeserializer deserializes and aggregates data for a certain parking
 */
class ParkingDeserializer
{
    private $parking_number;
    private $filesystem;

    /**
     * ParkingDeserializer constructor.
     * @param $parking_number: Number of the parking this deserializer is associated with.
     * @param $dir: Directory with serialized parking data.
     */
    public function __construct($parking_number, $dir) {
        $this->parking_number = $parking_number;
        $adapter = new Local($dir);
        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * Get all filenames for a certain base name (date)
     * @param $base_filename
     * @return array
     */
    private function get_files_for_base($base_filename) {
        $files = $this->filesystem->listContents();
        $filenames = array();
        foreach ($files as $file) {
            if (substr($file['basename'], 0, strlen($base_filename)) === $base_filename) {
                array_push($filenames, $file['basename']);
            }
        }
        return $filenames;
    }

    /**
     * Aggregate all data for this parking from files with a certain base name (date).
     * Result has following structure:
     *      description => parking description
     *      total_spaces => total amount of spaces in parking
     *      [timestamp] => amount of vacant spaces at [timestamp]
     * timestamp syntax is hhmmss
     * @param $base_filename
     * @return array
     */
    public function aggregate_for_base($base_filename) {
        $result = array();
        $filenames = $this->get_files_for_base($base_filename);

        foreach ($filenames as $filename) {
            $lines = explode(PHP_EOL, $this->filesystem->read($filename));
            foreach ($lines as $line) {
                $data = unserialize($line);
                if ($data) {
                    $this_parking_data = $data["parking_" . $this->parking_number];
                    if ($data["type"] === "static") {
                        $result["description"] = $this_parking_data["description"];
                        $result["total_spaces"] = $this_parking_data["total_spaces"];
                    } else if ($data["type"] === "dynamic") {
                        $vacant = $this_parking_data;
                        $time = $data["time"];
                        $result[$time] = $vacant;
                    }
                }
            }
        }

        return $result;
    }

}