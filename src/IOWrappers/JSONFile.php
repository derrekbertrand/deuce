<?php

namespace DerrekBertrand\Deuce\IOWrappers;

use DerrekBertrand\Deuce\IOWrappers\IOWrapperInterface as IOWrapper;
use Illuminate\Support\Collection;

class JSONFile implements IOWrapper
{
    protected $table;
    protected $directory;
    protected $linesize;
    protected $full_path;
    protected $fh;

    public static function make($table)
    {
        return new static($table);
    }

    protected function __construct($table)
    {
        $this->table = $table;
        $this->directory = config('deuce.directory');
        $this->linesize = config('deuce.linesize');

        //recursively make whatever directory we need
        //it will fail on write if we don't have permissions
        @mkdir($this->directory, 0755, true);

        //construct the full path
        $this->full_path = $this->directory.$this->table.'.json';
    }

    public function loadRows($chunksize, callable $cb)
    {
        $this->open('r');

        //make sure only the new data is loaded
        //loadRows should only be called once per table, so this should be fine
        \DB::table($this->table)->truncate();

        $arr = new Collection;
        $in = true;

        while($in !== false)
        {
            while($in !== false && count($arr) <= $chunksize)
            {
                $in = $this->gets();

                //clean up and get an array from the line, add it to our bulk add
                $tmp_arr = json_decode(rtrim($in, ",\n"), true, 2, JSON_BIGINT_AS_STRING);

                //array probably means we got the data we wanted
                if(is_array($tmp_arr))
                    $arr[] = $tmp_arr;
            }

            //run the bulk insert and empty the buffer array
            $cb($arr);
            $arr = new Collection;
        }
    }

    public function dumpRows(Collection $rows)
    {
        $this->open('w+');

        static $chunk_i = 0;

        $rowcount = count($rows);
        //serialize each row as json
        for ($i = 0; $i < $rowcount; $i++) {
            //if $chunk_i and $i are both 0, we shouldn't put a comma
            //further if either is non-zero, add a comma
            if ($chunk_i || $i) {
                $out = ",\n";
            } else {
                $out = '';
            }

            $out .= json_encode($rows[$i],
                JSON_HEX_APOS | JSON_HEX_QUOT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE
            );

            $this->write($out);
        }
        $chunk_i++;
    }

    public function __destruct()
    {
        //make sure the handle is closed
        $this->close();
    }

    protected function open($mode)
    {
        if($this->fh !== null)
            return $this->fh;

        //todo: check for errors
        $this->fh = fopen($this->full_path, $mode);

        return $this->fh;
    }

    protected function close()
    {
        //todo: handle errors
        if($this->fh == null)
            return true;
        else
            return fclose($this->fh);
    }

    protected function gets()
    {
        //todo: handle errors
        return fgets($this->fh, $this->linesize);
    }

    protected function write($data)
    {
        //todo: handle error
        return fwrite($this->fh, $data, strlen($data));
    }
}