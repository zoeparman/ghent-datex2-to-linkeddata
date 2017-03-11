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

    /**
     * @return string: Filename for current chunk
     */
    private function current_filename() {
        return $this->base_filename . '-' . $this->current_fragment;
    }

    // TODO can flysystem append to files?
    // TODO keep buffer as property
    // TODO the size check should happen before writing
    // TODO error/warning if content length is bigger than chunk size
    /**
     * Appends content to the fragmented file. Creates new chunk when file size limit is reached.
     * @param $content: The content to append to the file.
     */
    public function write($content) {
        if ($this->filesystem->has($this->current_filename())) {
            $content = $this->filesystem->read($this->current_filename()) . $content . "\n";
        }
        $this->filesystem->put($this->current_filename(), $content);

        $size = strlen($content); // Flysystem filesize doesn't update accurately. Probably disk caching.

        if ($size > $this->max_file_size) {
            $this->current_fragment++;
        }
    }
}