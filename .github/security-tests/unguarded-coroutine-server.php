<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/vendor/autoload.php';
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
final class CoroutineRaceAccount extends Model {
    protected $table = 'accounts';
    protected $guarded = ['is_admin'];
    protected $casts = ['is_admin' => 'boolean', 'observer_seen' => 'boolean'];
    public $timestamps = false;
}
$ready = false;
$capsule = new Capsule(new Container);
$server = new Swoole\Http\Server('127.0.0.1', 9501);
$server->set(['worker_num'=>1,'enable_coroutine'=>true,'hook_flags'=>SWOOLE_HOOK_ALL]);
$server->on('WorkerStart', function () use (&$ready,$capsule): void {
    $config = [
        'driver'=>'mysql','host'=>getenv('DB_HOST') ?: '127.0.0.1',
        'port'=>getenv('DB_PORT') ?: '3306','database'=>getenv('DB_DATABASE') ?: 'laravel_test',
        'username'=>getenv('DB_USERNAME') ?: 'root','password'=>getenv('DB_PASSWORD') ?: 'password',
        'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true,
    ];
    foreach (['default','force','ordinary','quiet','observer'] as $name) $capsule->addConnection($config,$name);
    $capsule->setEventDispatcher(new Dispatcher($capsule->getContainer()));
    $capsule->setAsGlobal(); $capsule->bootEloquent();
    $capsule->getConnection('default')->getSchemaBuilder()->dropIfExists('accounts');
    $capsule->getConnection('default')->getSchemaBuilder()->create('accounts',function($table):void{
        $table->increments('id'); $table->string('name');
        $table->boolean('is_admin')->default(false); $table->boolean('observer_seen')->default(false);
    });
    CoroutineRaceAccount::creating(function(CoroutineRaceAccount $account) use($capsule):void {
        if ($account->name==='force-request') {
            // This query uses the force request's dedicated PDO connection.
            $capsule->getConnection($account->getConnectionName())->select('SELECT SLEEP(1.5)');
        }
        if ($account->name==='ordinary-quiet') $account->observer_seen=true;
    });
    $ready=true;
});
$server->on('request',function($request,$response)use(&$ready,$capsule):void{
    if(!$ready){$response->status(503);$response->end('worker not ready');return;}
    try {
        switch($request->server['request_uri']??'/'){
            case '/health': $response->header('Content-Type','application/json');$response->end(json_encode(['ready'=>true]));return;
            case '/baseline':
                $m=CoroutineRaceAccount::on('default')->create(['name'=>'baseline','is_admin'=>true]);
                $response->header('Content-Type','application/json');$response->end(json_encode(['name'=>$m->name,'is_admin'=>(int)$m->is_admin,'observer_seen'=>(int)$m->observer_seen]));return;
            case '/force':
                CoroutineRaceAccount::on('force')->forceCreate(['name'=>'force-request','is_admin'=>false]);
                $response->header('Content-Type','application/json');$response->end(json_encode(['force_complete'=>true]));return;
            case '/ordinary':
                $m=CoroutineRaceAccount::on('ordinary')->create(['name'=>'ordinary-request','is_admin'=>true]);
                $response->header('Content-Type','application/json');$response->end(json_encode(['name'=>$m->name,'is_admin'=>(int)$m->is_admin]));return;
            case '/quiet':
                Model::withoutEvents(function()use($capsule):void{$capsule->getConnection('quiet')->select('SELECT SLEEP(1.5)');});
                $response->header('Content-Type','application/json');$response->end(json_encode(['quiet_complete'=>true]));return;
            case '/ordinary-quiet':
                $m=CoroutineRaceAccount::on('observer')->create(['name'=>'ordinary-quiet','is_admin'=>false]);
                $response->header('Content-Type','application/json');$response->end(json_encode(['name'=>$m->name,'observer_seen'=>(int)$m->observer_seen]));return;
            default:$response->status(404);$response->end('not found');
        }
    }catch(Throwable $e){$response->status(500);$response->end(json_encode(['error'=>$e::class,'message'=>$e->getMessage()]));}
});
$server->start();
