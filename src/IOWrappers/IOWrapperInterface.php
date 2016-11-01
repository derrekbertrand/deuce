<?php

namespace DerrekBertrand\Deuce\IOWrappers;

use Illuminate\Support\Collection;

interface IOWrapperInterface {
    static function make($table);
    public function dumpRows(Collection $rows);
    public function loadRows($chunksize, callable $cb);
}
