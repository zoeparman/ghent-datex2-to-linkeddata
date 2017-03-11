<?php
/**
 * Created by PhpStorm.
 * User: faplord
 * Date: 11/03/17
 * Time: 18:28
 */

namespace otn\linkeddatex2\gather;

use \League\Flysystem\Adapter\Local;
use \League\Flysystem\Filesystem;

class FragmentedWriter
{
    private $current_fragment;
    private $base_filename;
    private $filesystem;
    private $max_file_size;

    public function __construct($base_filename, $dir, $max_file_size)
    {
        $this->base_filename = $base_filename;
        $adapter = new Local($dir);
        $this->filesystem = new Filesystem($adapter);
        $this->max_file_size = $max_file_size;

        $this->current_fragment = 0;
        while ($this->filesystem->has($this->current_filename())) {
            $this->current_fragment++;
        }
    }

    private function current_filename() {
        return $this->base_filename . '-' . $this->current_fragment;
    }

    // TODO can flysystem append to files?
    // TODO keep buffer as property
    public function write($content) {
        if ($this->filesystem->has($this->current_filename())) {
            $content = $this->filesystem->read($this->current_filename()) . "\n" . $content;
        }
        $this->filesystem->put($this->current_filename(), $content);

        $size = strlen($content); // Flysystem filesize doesn't update accurately. Probably disk caching.

        if ($size > $this->max_file_size) {
            $this->current_fragment++;
        }
    }
}