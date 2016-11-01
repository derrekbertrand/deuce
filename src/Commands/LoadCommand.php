<?php

namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;
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
    protected $description = 'Load configured tables back from JSON.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->chunksize = config('deuce.chunksize');
        $this->filewrapper = config('deuce.filewrapper');
    }

    public function processRows($table)
    {
        //open for read
        $h = $this->filewrapper::make($table, false);

        //ensure we remove any existing data
        \DB::statement("delete from $table where 1");

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
    }
}
