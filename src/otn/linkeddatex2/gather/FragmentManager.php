<?php

namespace otn\linkeddatex2\gather;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

/**
 * Class FragmentedWriter
 * @package otn\linkeddatex2\gather
 * A file writer using FlySystem that splits a stream of data into equally sized chunks
 * using a [basename]-[chunk_number] format.
 */
class FragmentManager
{
    private $current_fragment;
    private $wrapped_fragments;
    private $base_filename;
    private $filesystem;
    private $max_file_size;

    /**
     * FragmentedWriter constructor.
     * @param $base_filename: Basename of the files
     * @param $dir: Destination directory
     * @param $max_chunk_size: Maximum size of file fragments (chunks)
     */
    public function __construct($base_filename, $dir, $max_chunk_size)
    {
        $this->base_filename = $base_filename;
        $adapter = new Local($dir);
        $this->filesystem = new Filesystem($adapter);
        $this->max_file_size = $max_chunk_size;

        $this->current_fragment = 0;
        $this->wrapped_fragments = array();
        while ($this->filesystem->has($this->current_filename())) {
            array_push($this->wrapped_fragments, $this->current_filename());
            $this->current_fragment++;
        }
        if ($this->current_fragment > 0) {
            $this->current_fragment--; // This will make it append to the last file if it isn't too big yet
            array_pop($this->wrapped_fragments);
        }
    }

    public function get_wrapped_fragments() {
        return $this->wrapped_fragments;
    }

    // TODO this is code duplication with ParkingDeserializer.php
    public function clear_directory() {
        $files = $this->filesystem->listContents();
        foreach ($files as $file) {
            if (substr($file['basename'], 0, strlen($this->base_filename)) === $this->base_filename) {
                $this->filesystem->delete($file['basename']);
            }
        }
        $this->current_fragment = 0;
    }

    /**
     * @return string: Filename for current chunk
     */
    private function current_filename() {
        return $this->base_filename . '-' . $this->current_fragment;
    }

    // TODO can flysystem append to files?
    // TODO keep buffer as property
    /**
     * Appends content to the fragmented file. Creates new chunk when file size limit is reached.
     * @param $content: The content to append to the file.
     * @return true if a fragment was wrapped up
     */
    public function write($content) {
        $current_content = "";
        if ($this->filesystem->has($this->current_filename())) {
            $current_content = $this->filesystem->read($this->current_filename());
        }

        $wrapped_fragment = false;
        if (strlen($content) > $this->max_file_size) {
            // If the content itself is bigger than the fragment size, write it but throw a warning
            trigger_error("Content is bigger than the configured chunk size");
            array_push($this->wrapped_fragments, $this->current_filename());
            $this->current_fragment++;
            $wrapped_fragment = true;
        } else if (strlen($current_content) + strlen($content) > $this->max_file_size) {
            // If it's not, but it doesn't fit in the fragment, create a new file
            array_push($this->wrapped_fragments, $this->current_filename());
            $this->current_fragment++;
            $wrapped_fragment = true;
        } else {
            // If it still fits, append it
            $content = $current_content . $content;
        }

        $this->filesystem->put($this->current_filename(), $content . "\n");
        return $wrapped_fragment;
    }

    public static function deploy($read_dir, $publish_dir, $fragment_filename) {
        // Init filesystem
        $read_adapter = new Local($read_dir);
        $read_filesystem = new Filesystem($read_adapter);
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

        // JSON encode to public directory
        $publish_adapter = new Local($publish_dir);
        $publish_filesystem = new Filesystem($publish_adapter);
        if ($publish_filesystem->has("current.json")) {
            $new_filename = time() . ".json";
            $publish_filesystem->rename("current.json", $new_filename);
            $wrapped_data["previous"] = "public/parking/" . $new_filename;
        }
        $publish_filesystem->write("current.json", json_encode($wrapped_data));
    }
}