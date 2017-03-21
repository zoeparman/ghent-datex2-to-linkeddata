<?php

namespace otn\linkeddatex2\gather;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

class ParkingHistoryFilesystem
{
    private $out_fs;
    private $res_fs;

    public function __construct($out_dirname, $res_dirname) {
        $out_adapter = new Local($out_dirname);
        $this->out_fs = new Filesystem($out_adapter);
        $res_adapter = new Local($res_dirname);
        $this->res_fs = new Filesystem($res_adapter);

        if (!$this->res_fs->has("static_data.turtle")) {
            $this->refresh_static_data();
        }
    }

    public function get_page($page_name) {
        // TODO
    }

    public function get_next_for_page($page_name) {
        // TODO
    }

    public function get_prev_for_page($page_name) {
        // TODO
    }

    public function get_page_for_timestamp($timestamp) {
        // TODO
    }

    public function get_last_page() {
        // TODO
    }

    // TODO use ParkingStatusOriginTime when available (see GhentToRDF.php)
    public function write_measurement($timestamp, $graph) {
        // Save the oldest filename to resources to avoid linear searching in filenames
        if (!$this->res_fs->has("oldest_filename")) {
            // TODO write this measurements filename as oldest filename
        }
        // TODO
    }

    public function refresh_static_data() {
        $graph = GraphProcessor::get_static_data();
        $this->res_fs->write("static_data.turtle", $graph->serialise("turtle"));
    }
}