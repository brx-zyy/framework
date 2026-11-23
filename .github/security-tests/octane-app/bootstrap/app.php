<?php
use Illuminate\Foundation\Application;
use Laravel\Octane\OctaneServiceProvider;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: dirname(__DIR__).'/routes/web.php')
    ->withProviders([OctaneServiceProvider::class])
    ->create();
