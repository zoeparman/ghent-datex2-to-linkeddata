<?php

namespace otn\linkeddatex2\gather;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

// TODO POSSIBLY DEPRECATED

/**
 * Class FragmentedWriter
 * @package otn\linkeddatex2\gather
 * A file writer using FlySystem that splits a stream of data into equally sized chunks.
 * The filename is a timestamp that forms a lower bound on the times the data was written in the file.
 */
class DeployingWriter
{
    private $tmp_filesystem;
    private $deployment_filesystem;
    private $tmp_filename;
    private $max_chunk_size;
    private $deployment_metadata;

    /**
     * FragmentedWriter constructor.
     * @param $deployment_dir: the directory to deploy linked files
     * @param $max_chunk_size: Maximum size of file fragments (chunks)
     */
    public function __construct($deployment_dir, $max_chunk_size)
    {
        $this->tmp_filename = "ghent-datex2-to-linkeddata-fragment.tmp";
        $this->max_chunk_size = $max_chunk_size;

        $tmp_adapter = new Local("/tmp");
        $this->tmp_filesystem = new Filesystem($tmp_adapter);

        $deployment_adapter = new Local($deployment_dir);
        $this->deployment_filesystem = new Filesystem($deployment_adapter);


    }

    public function set_deployment_metadata($metadata) {
        $this->deployment_metadata = $metadata;
    }

    // TODO can flysystem append to files?
    /**
     * Appends content to the fragmented file. Creates new chunk when file size limit is reached.
     * @param $content: The content to append to the file.
     */
    public function write($content) {
        $current_content = "";
        if ($this->tmp_filesystem->has($this->tmp_filename)) {
            $current_content = $this->tmp_filesystem->read($this->tmp_filename);
        }

        if (strlen($content) > $this->max_chunk_size) {
            // If the content itself is bigger than the fragment size, write it to tmp but throw a warning
            // This will immediately be deployed on next call
            trigger_error("Content is bigger than the configured chunk size");
            if ($this->tmp_filesystem->has($this->tmp_filename)) {
                // If there was a previous tmp file, deploy it
                $this->deploy($this->tmp_filesystem, $this->deployment_filesystem, $this->tmp_filename);
            }
        } else if (strlen($current_content) + strlen($content) > $this->max_chunk_size) {
            // If it's not, but it doesn't fit in the fragment, deploy and create a new file
            $this->deploy($this->tmp_filesystem, $this->deployment_filesystem, $this->tmp_filename);
        } else {
            // If it still fits, append it
            $content = $current_content . $content;
        }

        $this->tmp_filesystem->put($this->tmp_filename, $content . "\n");
    }

    private function deploy(Filesystem $read_filesystem, Filesystem $publish_filesystem, $fragment_filename) {
        print("Deploying\n");
        // Init filesystem
        $lines = explode(PHP_EOL, $read_filesystem->read($fragment_filename));

        // Wrap all lines
        $wrapped_data = array();
        foreach($lines as $line) {
            if (strlen($line) > 0) {
                $data_complete = json_decode($line, true);
                $data_parkings = array();
                foreach($data_complete as $entry => $value) {
                    if (substr($entry, 0, strlen("parking_")) === "parking_") {
                        $data_parkings[$entry] = $value;
                    }
                }
                $wrapped_data[$data_complete["time"]] = $data_parkings;
            }
        }

        // Add deployment metadata
        $wrapped_data["metadata"] = $this->deployment_metadata;

        // Rename previous and add link if previous exists
        if ($publish_filesystem->has("current.json")) {
            $new_filename = time() . ".json";
            $publish_filesystem->rename("current.json", $new_filename);
            $wrapped_data["previous"] = "public/parking/" . $new_filename;
        }

        // Publish new file and delete temporary
        $publish_filesystem->put("current.json", json_encode($wrapped_data, JSON_PRETTY_PRINT));
        $read_filesystem->delete($this->tmp_filename);
    }
}