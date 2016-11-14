<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        //create the app instance to base our tests on
        $app = new Illuminate\Foundation\Application(
            realpath(__DIR__.'/../')
        );

        //we need to define our kernel, which is just a stock kernel
        $app->singleton(
            Illuminate\Contracts\Console\Kernel::class,
            Illuminate\Foundation\Console\Kernel::class
        );

        //we need to define our kernel, which is just a stock kernel
        $app->singleton(
            Illuminate\Contracts\Http\Kernel::class,
            Illuminate\Foundation\Http\Kernel::class
        );

        //we provide the default exeption handler too
        $app->singleton(
            Illuminate\Contracts\Debug\ExceptionHandler::class,
            Illuminate\Foundation\Exceptions\Handler::class
        );

        //we now bootstrap a Console app to pass back
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
