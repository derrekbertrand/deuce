# Deuce

*A quick and dirty database dump and restore for Laravel 5.*


## Installation

In your Laravel `config/app.php` file, add this to the service providers array:

    'DerrekBertrand\Deuce\Providers\DeuceProvider::class',

Then run this to publish the `config/deuce.php` file:

    php artisan vendor:publish --provider=DerrekBertrand\\Deuce\\Providers\\DeuceProvider

You should be able to add tables as needed to the config file. Look in this
file for help with configuring Deuce.

## Commands

### Dumping

    php artisan deuce:dump

The dump command writes all tables configured in `config/deuce.php` to a folder
as GZipped JSON files.

### Loading

    php artisan deuce:load

The load command looks for the files written earlier and writes them to the
default database connection.

## Gotchas

- This package is *not* considered stable. Please don't rely on this for
  production.
- Currently the row size (including JSON meta data) is hard coded at 4K.
  Anything bigger than this will break.

## Todo

- Code comments: code needs proper comment blocks
- Code cleanup: improve readability; catch more errors
- Table level config: allow config settings to be overridden on a table level.
  This will allow admins to further optimize their backups.
- Finish Load command
- Have the file wrapper load directory and linesize if needed; no need to pass
  it around in the command when we don't even use it
- Consider removing file specific code from command. The library will be much
  more flexible if we let the file wrapper take care of formatting. Who knows,
  in the future this may not even be file backed, but a method of DB sync/
  Amazon S3 backed / or import from more obscure formats.
- Unit tests?
