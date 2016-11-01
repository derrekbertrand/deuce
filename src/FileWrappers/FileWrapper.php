<?php

namespace DerrekBertrand\Deuce\FileWrappers;

interface FileWrapper {
    static function make($table, $write);
    function gets();
    function write($data);
}
