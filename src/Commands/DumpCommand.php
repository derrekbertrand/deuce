<?php
namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;
use DerrekBertrand\Deuce\Commands\ProcessesTablesTrait as ProcessesTables;
use DerrekBertrand\Deuce\Commands\ProcessesRowsInterface as ProcessesRows;

class DumpCommand extends Command implements ProcessesRows
{
    use ProcessesTables;

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

        $this->chunksize = config('deuce.chunksize');
        $this->filewrapper = config('deuce.filewrapper');
    }

    public function processRows($table)
    {
        //open file for writing
        $h = $this->filewrapper::make($table, true);

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
