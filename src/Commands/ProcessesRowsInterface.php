<?php

namespace DerrekBertrand\Deuce\Commands;

interface ProcessesRowsInterface
{
    /**
     * Process the rows of the table, whatever that means.
     *
     * This method is expected to receive a table name to process (be that
     * reading or writing is implementation dependent). It can be assured
     * that any exceptions thrown are printed and the process is appropriately
     * aborted. This method more or less replaces 'handle' and results in less
     * boilerplate code.
     *
     * @param string $table
     *
     * @return void
     */
    public function processRows($table);
}