<?php

namespace DerrekBertrand\Deuce\Commands;
use Illuminate\Database\QueryException;

/**
 * Makes the handle method for Dump and Load.
 *
 * Reconciles arguments, checks maintenance mode, provides the main loop,
 * and provides a catch-all for any loose exceptions.
 *
 * Expects and calls processRows() with the name of the table to work on.
 * The exact operation is handled by dump/load command class.
 */
trait ProcessesTablesTrait
{
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //use the specified tables over the config defaults
        if (count($this->option('table'))) {
            $tables = $this->option('table');
        } else {
            $tables = config('deuce.tables');
        }

        if (!\App::isDownForMaintenance()) {
            $this->error('App must be in maintenance mode to help avoid DB locks.'
                . PHP_EOL
                . 'Please ensure that no process is writing to the DB while this command runs.');
            return 1;
        }

        //go through and process each table
        foreach ($tables as $table) {
            //let the user know what model we're on
            $this->info("Processing $table");

            try {
                $this->processRows($table);
            } catch (QueryException $e)
            {
                $this->error('QueryException: the table might not exist, or your chunk size is too big for SQL to process.');
                return 2;
            } catch (\Exception $e) {
                //print the message
                $this->error(get_class($e)." while processing $table:"
                    . PHP_EOL
                    . $e->getMessage()
                );

                //don't say we finished it, because we didn't
                return -1;
            }

            //tell the user everything is okay in the world
            $this->info("  Finished $table");
        }

        $this->warn('Peak memory consumption: '.intval(memory_get_peak_usage()/1024).'KiB');
        return 0;
    }
}
