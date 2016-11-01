<?php
namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;

class DumpCommand extends Command
{

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump configured tables as JSON.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deuce:dump
                            {--T|table=* : The table names to operate on}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->tables    = config('deuce.tables');
        $this->chunksize = config('deuce.chunksize');
        $this->directory = config('deuce.directory');
        $this->filewrapper = config('deuce.filewrapper');
        $this->linesize = config('deuce.linesize');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //use the specified tables over the config defaults
        if(count($this->option('table')))
            $this->tables = $this->option('table');

        if (!\App::isDownForMaintenance()) {
            $this->error('App must be in maintenance mode to help avoid DB locks.'
                . PHP_EOL
                . 'Please ensure that no process is writing to the DB while this command runs.');
            return;
        }

        //go through and dump each table
        foreach ($this->tables as $table) {
            $this->handleTable($table);
        }
    }

    protected function handleTable($table)
    {
        //let the user know what model we're on
        $this->info("Dumping $table.");

        //open file for writing
        $h = $this->filewrapper::make($table, true);

        try {
            //attempt
            $this->writeChunks($h, $table);
        } catch (\Exception $e) {
            //print the message
            $this->error("Error while processing $table."
                . PHP_EOL
                . $e->getMessage()
            );
        }
    }

    protected function writeChunks($h, $table)
    {
        $chunk_i = 0; //keep track of the number of chunks

        $h->write("[\n");

        do {
            $rows = \DB::select("select * from $table limit ? OFFSET ?", [$this->chunksize, $chunk_i * $this->chunksize]);

            $rowcount = count($rows);
            //serialize each row as json
            for ($i = 0; $i < $rowcount; $i++) {
                //if $chunk_i and $i are both 0, we shouldn't put a comma
                //further if either is non-zero, add a comma
                if ($chunk_i || $i) {
                    $out = ",\n";
                } else {
                    $out = '';
                }

                $out .= json_encode($rows[$i],
                    JSON_HEX_APOS | JSON_HEX_QUOT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE
                );

                $h->write($out);
            }

            $chunk_i++;
        } while (count($rows) == $this->chunksize);

        $h->write("\n]");
    }
}
