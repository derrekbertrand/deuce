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
    | GZip compression causes a small amount of processing overhead, but saves
    | your disk space.
    |
    */

    'chunksize' => env('DEUCE_CHUNKSIZE', 200),
    'gzip' => env('DEUCE_GZIP', true),
];
