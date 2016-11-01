<?php

namespace DerrekBertrand\Deuce\FileWrappers;

use DerrekBertrand\Deuce\FileWrappers\FileWrapper;

class GZipFile implements FileWrapper
{
    protected $table;
    protected $directory;
    protected $linesize;
    protected $full_path;
    protected $op_write;
    protected $fh = null;

    public static function make($table, $write)
    {
        return new static($table, $write);
    }

    protected function __construct($table, $write)
    {
        $this->table = $table;
        $this->directory = config('deuce.directory');
        $this->linesize = config('deuce.linesize');
        $this->op_write = boolval($write);

        //recursively make whatever directory we need
        //it will fail on write if we don't have permissions
        @mkdir($this->directory, 0755, true);

        //construct the full path
        $this->full_path = $this->directory.$this->table.'.json.gz';

        $this->open();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function open()
    {
        //todo: check for errors
        if ($this->op_write)
            $this->fh = gzopen($this->full_path, 'w9');
        else
            $this->fh = gzopen($this->full_path, 'r');

        return $this;
    }

    public function close()
    {
        //todo: handle errors
        if($this->fh == null)
            return true;
        else
            return gzclose($this->fh);
    }

    public function gets()
    {
        if($this->op_write)
            throw new \Exception('Tried to read a file opened for writing. That action is not supported.');
        //todo: handle errors
        return gzgets($this->fh, $this->linesize);
    }

    public function write($data)
    {
        if(!$this->op_write)
            throw new \Exception('Tried to write to a file opened for reading. That action is not supported.');
        //todo: handle error
        return gzwrite($this->fh, $data, strlen($data));
    }
}
