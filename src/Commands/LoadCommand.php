<?php

namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use DerrekBertrand\Deuce\Commands\ProcessesTablesTrait as ProcessesTables;
use DerrekBertrand\Deuce\Commands\ProcessesRowsInterface as ProcessesRows;

class LoadCommand extends Command implements ProcessesRows
{
    use ProcessesTables;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deuce:load
                            {--T|table=* : The table names to operate on}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load configured tables back from a resource.';

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
     * Process the load.
     *
     * For the load implementation, we ask the IOwrapper to chunk rows and pass
     * them back to us for processing.
     *
     * @param string $table
     * @return void
     */
    public function processRows($table)
    {
        //open for read
        $h = $this->iowrapper::make($table);

        //use the handle to load batches of rows
        $h->loadRows($this->chunksize, function (Collection $rows) use ($table) {
            \DB::table($table)->insert($rows->all());
        });
    }
}
