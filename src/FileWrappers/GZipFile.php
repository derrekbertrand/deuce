<?php

namespace DerrekBertrand\Deuce\FileWrappers;

use DerrekBertrand\Deuce\FileWrappers\FileWrapper;

class GZipFile implements FileWrapper
{
    protected $table;
    protected $directory;
    protected $linesize;
    protected $full_path;
    protected $fh = null;

    public static function make($table, $directory, $linesize)
    {
        return new static($table, $directory, $linesize);
    }

    protected function __construct($table, $directory, $linesize)
    {
        $this->table = $table;
        $this->directory = $directory;
        $this->linesize = $linesize;

        //recursively make whatever directory we need
        //it will fail on write if we don't have permissions
        @mkdir($this->directory, 0755, true);

        //construct the full path
        $this->full_path = $this->directory.$this->table.'.json.gz';
    }

    public function fopen($write)
    {
        //todo: check for errors
        if ($write)
            $this->fh = gzopen($this->full_path, 'w9');
        else
            $this->fh = gzopen($this->full_path, 'r');

        return $this;
    }

    public function fclose()
    {
        //todo: handle errors
        if($this->fh == null)
            return true;
        else
            return gzclose($this->fh);
    }

    public function fgets()
    {
        //todo: handle errors
        return gzgets($this->fh, $this->linesize);
    }

    public function fwrite($data)
    {
        //todo: handle error
        return gzwrite($this->fh, $data, strlen($data));
    }
}
