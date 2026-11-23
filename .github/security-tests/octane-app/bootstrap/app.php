<?php
use Illuminate\Foundation\Application;
use Laravel\Octane\OctaneServiceProvider;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(using: function () { require dirname(__DIR__).'/routes/web.php'; })
    ->withKernels()
    ->withExceptions()
    ->withProviders([OctaneServiceProvider::class])
    ->create();
