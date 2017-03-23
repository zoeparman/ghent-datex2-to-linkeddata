<?php

namespace otn\linkeddatex2\gather;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

class ParkingHistoryFilesystem
{
    private $out_fs;
    private $res_fs;
    private $basename_length;
    private $minute_interval;
    private $newest_timestamp;

    public function __construct($out_dirname, $res_dirname) {
        date_default_timezone_set("Europe/Brussels"); // TODO is this still necessary?
        $out_adapter = new Local($out_dirname);
        $this->out_fs = new Filesystem($out_adapter);
        $res_adapter = new Local($res_dirname);
        $this->res_fs = new Filesystem($res_adapter);
        $this->basename_length = 19;
        $this->minute_interval = 5;

        if (!$this->res_fs->has("static_data.turtle")) {
            $this->refresh_static_data();
        }
    }

    // Does this file exist?
    public function has_file($filename) {
        return $this->out_fs->has($filename);
    }

    // Get the contents of a file
    public function get_file_contents($filename) {
        if ($this->has_file($filename)) {
            return $this->out_fs->read($filename);
        }
        return false;
    }

    // Get page closest to requested timestamp
    public function get_closest_page_for_timestamp($timestamp) {
        if (!$this->has_file($this->get_filename_for_timestamp($timestamp))) {
            // TODO look for closest measurement?
            return $this->get_prev_for_timestamp($timestamp);
        }
        return $this->get_filename_for_timestamp($timestamp);
    }

    // Get next page for requested timestamp
    public function get_next_for_timestamp($timestamp) {
        $timestamp = $this->round_timestamp($timestamp);
        $newest = $this->get_newest_timestamp();
        while($timestamp < $newest) {
            $timestamp += $this->minute_interval*60;
            $filename = $this->get_filename_for_timestamp($timestamp);
            if ($this->out_fs->has($filename)) {
                return $filename;
            }
        }
        return false;
    }

    // Get previous page for requested timestamp (this is the previous page to page_for_timestamp)
    public function get_prev_for_timestamp($timestamp) {
        $oldest = $this->get_oldest_timestamp();
        if ($oldest) {
            $timestamp = $this->round_timestamp($timestamp);
            while ($timestamp > $oldest) {
                $timestamp -= $this->minute_interval*60;
                $filename = $this->get_filename_for_timestamp($timestamp);
                if ($this->out_fs->has($filename)) {
                    return $filename;
                }
            }
        }
        return false;
    }

    // Get the last written page
    public function get_last_page() {
        return $this->get_closest_page_for_timestamp($this->get_newest_timestamp());
    }

    // Write a measurement to a page
    public function write_measurement($timestamp, \EasyRdf_Graph $graph) {
        $rounded = $this->round_timestamp($timestamp);
        // Save the oldest filename to resources to avoid linear searching in filenames
        if (!$this->res_fs->has("oldest_timestamp")) {
            $this->res_fs->write("oldest_timestamp", $rounded);
        }
        $this->newest_timestamp = $timestamp;

        $filename = $this->get_filename_for_timestamp($timestamp);

        // TODO add graph using sensor ontology (ParkingStatusOriginTime, see GhentToRDF.php)
        $contents = "";
        if ($this->out_fs->has($filename)) {
            $contents = $this->out_fs->read($filename) . "\n";
        }
        $this->out_fs->put($filename, $contents . $graph->serialise("turtle"));
    }

    // Refresh the static data
    public function refresh_static_data() {
        $graph = GraphProcessor::get_static_data();
        $this->res_fs->write("static_data.turtle", $graph->serialise("turtle"));
    }

    // PRIVATE METHODS

    // Round a timestamp to its respective file timestamp
    private function round_timestamp($timestamp) {
        $minutes = date('i', $timestamp);
        $seconds = date('s', $timestamp);
        $timestamp -= ($minutes%5)*60 + $seconds;
        return $timestamp;
    }

    // Get the oldest timestamp for which a file exists
    private function get_oldest_timestamp() {
        if ($this->res_fs->has("oldest_timestamp")) {
            return $this->res_fs->read("oldest_timestamp");
        }
        return false;
    }

    // Get the latest timestamp for which a file exists
    private function get_newest_timestamp() {
        return $this->newest_timestamp;
    }

    // Get appropriate filename for given timestamp
    private function get_filename_for_timestamp($timestamp) {
        return substr(date('c', $this->round_timestamp($timestamp)), 0, $this->basename_length) . ".turtle";
    }
}