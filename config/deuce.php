<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dumped Tables
    |--------------------------------------------------------------------------
    |
    | Here you may specify what tables to dump and in what order they need to
    | be done. When they are loaded back into a DB, they will be loaded in the
    | order specified below. This is important if you have foreign keys that
    | need to remain intact.
    |
    | If, for example, you had `posts` with a foreign key `user_id`, you would
    | want to dump the `users` table first. This way when `posts` is
    | repopulated into the database, the referenced `users` are already there.
    |
    */

    'tables' => [
        'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dump Folder
    |--------------------------------------------------------------------------
    |
    | Here you tell us where to dump the data.
    |
    | The data will be in `table_name.json` or `table_name.json.gz` depending
    | on your configuration.
    |
    */

    'directory' => env('DEUCE_DIR', database_path('deuce/')),

    /*
    |--------------------------------------------------------------------------
    | Optimization Options
    |--------------------------------------------------------------------------
    |
    | If your PHP configuration has enough memory, you can probably increase
    | the chunk size to get faster processing speeds. If your tables are large
    | or contain MEDIUMTEXT fields, you might have to reduce the chunk size
    | to avoid running out of memory.
    |
    | The file wrapper class decides what interface we use to manipulate the
    | backups.
    |
    | When reading files, each instance/row is placed on one line and the
    | program seeks to the end of the line to find the end of a row. For speed
    | and memory considerations, there is a maximum line length. If you have
    | XTEXT fields, binary, or other long fields you will probably need to 
    | increase linesize. Note that linesize is all the data AND the JSON
    | structural metadata for the row.
    */

    'chunksize' => env('DEUCE_CHUNKSIZE', 200),
    'filewrapper' => \DerrekBertrand\Deuce\FileWrappers\PlainFile::class,
    'linesize' => env('DEUCE_LINESIZE', 4096)
];
