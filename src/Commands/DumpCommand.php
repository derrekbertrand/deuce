<?php

namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;
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
    protected $description = 'Dump configured tables as JSON.';

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

    public function processRows($table)
    {
        $h = $this->iowrapper::make($table); //open for writing

        //use Laravel's chunking to process the chunks
        \DB::table($table)->chunk($this->chunksize, function($rows) use (&$h) {
            $h->dumpRows($rows);
        });
    }
}
