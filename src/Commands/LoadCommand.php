<?php

namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;

class LoadCommand extends Command
{

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
    protected $description = 'Load configured tables back from JSON.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->tables = config('deuce.tables');
        $this->chunksize = config('deuce.chunksize');
        $this->filewrapper = config('deuce.filewrapper');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!\App::isDownForMaintenance())
        {
            $this->error('App must be in maintenance mode to help avoid DB locks.'
                .PHP_EOL
                .'Please ensure that no process is writing to the DB while this command runs.');
            return;
        }

        //go through and undump each table
        foreach($this->tables as $table)
        {
            $this->handleTable($table);
        }
    }

    protected function handleTable($table)
    {
        //let the user know what model we're on
        $this->info("Loading $table");

        //open file for read
        $h = $this->filewrapper::make($table, false);

        try {
            \DB::statement("delete from $table where 1");

            //attempt to read into DB
            $this->readLines($h, $table);
        } catch(\Exception $e) {
            //print the message
            $this->error("Error while processing $table"
                .PHP_EOL
                .$e->getMessage()
            );
        }
    }

    protected function readLines($h, $table)
    {
        $arr = [];
        $chunk_i = 0;
        $in = $h->gets();

        if($in !== "[\n")
            throw new \Exception('Does not appear to be a valid JSON array!');

        while($in !== false)
        {
            while($in !== false && count($arr) < $this->chunksize)
            {
                $in = $h->gets();

                //clean up and get an array from the line, add it to our bulk add
                $tmp_arr = json_decode(substr($in, 0, strlen($in)-2), true, 2, JSON_BIGINT_AS_STRING);

                //array probably means we got the data we wanted
                if(is_array($tmp_arr))
                    $arr[] = $tmp_arr;
            }

            //run the bulk insert and empty the buffer array
            \DB::table($table)->insert($arr);
            $arr = [];

            $chunk_i++;
        }

        $this->info("  Finished $table.");
    }
}
