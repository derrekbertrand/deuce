<?php

namespace DerrekBertrand\Deuce\Commands;

use Illuminate\Console\Command;

class DumpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deuce:dump';

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

        $this->tables = config('deuce.tables');
        $this->chunksize = config('deuce.chunksize');
        $this->directory = config('deuce.directory');
        $this->gzip = config('deuce.gzip');
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

        //recursively make whatever directory we need
        //it will fail on write if we don't have permissions
        @mkdir($this->directory ,0755, true);

        //go through and dump each table
        foreach($this->tables as $table)
        {
            $this->handleTable($table);
        }
    }

    protected function handleTable($table)
    {
        $file = $this->directory.$table.'.json';

        if($this->gzip)
            $file .= '.gz';

        //let the user know what model we're on
        $this->info('Dumping '.basename($file));

        //get a handle on the file
        $h = $this->fopen($file);

        try {
            //attempt
            $this->writeChunks($h, $table);
        } catch(\Exception $e) {
            //print the message
            $this->error('Error while processing '.basename($file)
                .PHP_EOL
                .$e->getMessage()
            );
        } finally {
            //close the handle if not already closed
            $this->fclose($h);
        }
    }

    protected function writeChunks($h, $table)
    {
        $chunk_i = 0; //keep track of the number of chunks

        $this->fwrite($h, "[\n");

        do {
            $rows = \DB::select("select * from $table limit ? OFFSET ?", [$this->chunksize, $chunk_i*$this->chunksize]);


            $rowcount = count($rows);
            //serialize each row as json
            for($i = 0;$i < $rowcount; $i++)
            {
                //if $chunk_i and $i are both 0, we shouldn't put a comma
                //further if either is non-zero, add a comma
                if($chunk_i || $i)
                    $out = ",\n";
                else
                    $out = '';

                $out .= json_encode($rows[$i],
                    JSON_HEX_APOS | JSON_HEX_QUOT | JSON_BIGINT_AS_STRING |JSON_UNESCAPED_UNICODE
                );


                $this->fwrite($h, $out);
            }

            $this->info("  Wrote chunk #$chunk_i on $table.");
            $chunk_i++;
        } while(count($rows) == $this->chunksize);

        $this->fwrite($h, "\n]");
    }

    protected function fopen($file)
    {
        if($this->gzip)
            return gzopen($file, 'w9');
        else
            //todo: check for false
            return fopen($file, 'w+');
    }

    protected function fclose(&$handle)
    {
        //todo: handle errors
        if($handle == null)
            return true;

        if($this->gzip)
            return gzclose($handle);
        else
            return fclose($handle);
    }

    protected function fwrite($handle, $data)
    {
        //todo: handle error

        if($this->gzip)
            $ret = gzwrite($handle, $data, strlen($data));
        else
            $ret = fwrite($handle, $data, strlen($data));

        return $ret;
    }
}
