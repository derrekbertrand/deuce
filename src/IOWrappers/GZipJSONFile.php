<?php

namespace DerrekBertrand\Deuce\IOWrappers;

use DerrekBertrand\Deuce\IOWrappers\IOWrapperInterface as IOWrapper;
use DerrekBertrand\Deuce\IOWrappers\JSONFile;

class GZipJSONFile extends JSONFile implements IOWrapper
{
    protected function __construct($table)
    {
        parent::__construct($table);

        //same but with GZip
        $this->full_path .= '.gz';
    }

    protected function open($mode)
    {
        if($this->fh !== null)
            return $this->fh;

        //the write mode is a different signature
        if($mode === 'w+')
            $mode = 'w9';

        //todo: check for errors
        $this->fh = gzopen($this->full_path, $mode);

        return $this->fh;
    }

    protected function close()
    {
        //todo: handle errors
        if($this->fh == null)
            return true;
        else
            return gzclose($this->fh);
    }

    protected function gets()
    {
        //todo: handle errors
        return gzgets($this->fh, $this->linesize);
    }

    protected function write($data)
    {
        //todo: handle error
        return gzwrite($this->fh, $data, strlen($data));
    }
}
