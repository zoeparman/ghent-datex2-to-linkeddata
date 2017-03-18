<?php

namespace otn\linkeddatex2\gather;


class Aggregator
{
    private $json_dir;

    public function __construct($dir) {
        $this->json_dir = $dir;
    }

    // Pass times as YYYY-MM-DD hh:mm:ss
    public function aggregate($from, $to) {
        // check arguments:
        //      - to must be after from
        //      - from and to must correctly parse to timestamps
        // TODO THIS SHOULD BE A CLIENT SIDE APPLICATION
        $result = array();
        $from_timestamp = strtotime($from);

        // Use from timestamp to locate first file (timestamp = lower bound)

        // Keep moving on until current.json or $to datetime is found

        return $result;
    }
}