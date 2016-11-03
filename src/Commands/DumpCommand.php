<?php

namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use DerrekBertrand\Deuce\Commands\ProcessesTablesTrait as ProcessesTables;
use DerrekBertrand\Deuce\Commands\ProcessesRowsInterface as ProcessesRows;

class DumpCommand extends Command implements ProcessesRows
{
    use ProcessesTables;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deuce:dump
                            {--T|table=* : The table names to operate on}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump configured tables to a resource.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->chunksize = config('deuce.chunksize');
        $this->iowrapper = config('deuce.iowrapper');
    }

    /**
     * Process the dump.
     *
     * For the dump implementation, we chunk the DB and feed it to the
     * IOWrapper for processing.
     *
     * @param string $table
     * @return void
     */
    public function processRows($table)
    {
        //open for writing
        $h = $this->iowrapper::make($table);

        //use Laravel's chunking to process the chunks
        \DB::table($table)->chunk($this->chunksize, function(Collection $rows) use (&$h) {
            $h->dumpRows($rows);
        });
    }
}
