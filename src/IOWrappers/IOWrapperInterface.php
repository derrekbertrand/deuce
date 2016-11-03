<?php

namespace DerrekBertrand\Deuce\IOWrappers;

use Illuminate\Support\Collection;

interface IOWrapperInterface {

    /**
     * Make a new IOWrapperInterface instance based on a table name.
     *
     * @param string $table
     * @return IOWrapperInterface
     */
    static function make($table);

    /**
     * Take a Collection of rows and stream it to the dump location.
     *
     * Expects a collection from Laravel's DB Facade. Takes the data and should
     * stream it into whatever output destination the interface uses. This
     * could be a file, a gzipped file, a remote SQL server, or whatever else
     * you dream up.
     *
     * @param Collection $rows
     * @return void
     */
    public function dumpRows(Collection $rows);

    /**
     * Load a Collection of rows from a source and send them to be loaded.
     *
     * Expects a callback that takes a Collection of rows. Passes each set of
     * rows to the callback in sequence, where it is expected to be loaded
     * into the database. How it gets the rows is 
     *
     * @param int $chunk_size
     * @param callable $cb
     * @return void
     */
    public function loadRows($chunk_size, callable $cb);
}
