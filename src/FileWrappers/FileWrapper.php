<?php

namespace DerrekBertrand\Deuce\FileWrappers;

interface FileWrapper {
    function fopen($write);
    function fclose();
    function fgets();
    function fwrite($data);
}
