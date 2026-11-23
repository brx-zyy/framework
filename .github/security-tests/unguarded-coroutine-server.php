<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Swoole\Coroutine;

final class CoroutineRaceAccount extends Model
{
    protected $table = 'accounts';

    protected $guarded = ['is_admin'];

    public $timestamps = false;
}

$ready = false;

$server = new Swoole\Http\Server('127.0.0.1', 9501);
$server->set([
    'worker_num' => 1,
    'enable_coroutine' => true,
    'hook_flags' => SWOOLE_HOOK_ALL,
]);

$server->on('WorkerStart', function () use (&$ready): void {
    $capsule = new Capsule(new Container);
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    $capsule->setEventDispatcher(new Dispatcher($capsule->getContainer()));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $capsule->getConnection()->getSchemaBuilder()->create('accounts', function ($table): void {
        $table->increments('id');
        $table->string('name');
        $table->boolean('is_admin')->default(false);
    });

    // The callback models an application observer doing suspendable I/O.
    // The first request enters it while forceCreate() has globally disabled
    // mass-assignment protection. A second request can run before it returns.
    CoroutineRaceAccount::creating(function (CoroutineRaceAccount $account): void {
        if ($account->name === 'force-request') {
            Coroutine::sleep(1.2);
        }
    });

    $ready = true;
});

$server->on('request', function ($request, $response) use (&$ready): void {
    if (! $ready) {
        $response->status(503);
        $response->end('worker not ready');
        return;
    }

    try {
        switch ($request->server['request_uri'] ?? '/') {
            case '/health':
                $response->header('Content-Type', 'application/json');
                $response->end(json_encode(['ready' => true]));
                return;

            case '/baseline':
                $account = CoroutineRaceAccount::create([
                    'name' => 'baseline',
                    'is_admin' => true,
                ]);
                $response->header('Content-Type', 'application/json');
                $response->end(json_encode([
                    'name' => $account->name,
                    'is_admin' => (int) $account->is_admin,
                ]));
                return;

            case '/force':
                CoroutineRaceAccount::query()->forceCreate([
                    'name' => 'force-request',
                    'is_admin' => false,
                ]);
                $response->header('Content-Type', 'application/json');
                $response->end(json_encode(['force_complete' => true]));
                return;

            case '/ordinary':
                $account = CoroutineRaceAccount::create([
                    'name' => 'ordinary-request',
                    'is_admin' => true,
                ]);
                $response->header('Content-Type', 'application/json');
                $response->end(json_encode([
                    'name' => $account->name,
                    'is_admin' => (int) $account->is_admin,
                ]));
                return;

            default:
                $response->status(404);
                $response->end('not found');
        }
    } catch (Throwable $exception) {
        $response->status(500);
        $response->end(json_encode([
            'error' => $exception::class,
            'message' => $exception->getMessage(),
        ]));
    }
});

$server->start();
