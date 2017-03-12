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
class FragmentedWriter
{
    private $current_fragment;
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
        while ($this->filesystem->has($this->current_filename())) {
            $this->current_fragment++;
        }
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
     */
    public function write($content) {
        $current_content = "";
        if ($this->filesystem->has($this->current_filename())) {
            $current_content = $this->filesystem->read($this->current_filename());
        }

        if (strlen($content) > $this->max_file_size) {
            // If the content itself is bigger than the fragment size, write it but throw a warning
            trigger_error("Content is bigger than the configured chunk size");
            $this->current_fragment++;
        } else if (strlen($current_content) + strlen($content) > $this->max_file_size) {
            // If it's not, but it doesn't fit in the fragment, create a new file
            $this->current_fragment++;
        } else {
            // If it still fits, append it
            $content = $current_content . $content;
        }

        $this->filesystem->put($this->current_filename(), $content . "\n");
    }
}