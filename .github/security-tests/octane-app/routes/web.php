<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
final class OctaneRaceAccount extends Model
{
    protected $table = 'accounts';
    protected $guarded = ['is_admin'];
    protected $casts = ['is_admin' => 'boolean', 'observer_seen' => 'boolean'];
    public $timestamps = false;
}
OctaneRaceAccount::creating(function (OctaneRaceAccount $account): void {
    if ($account->name === 'force-request') DB::connection('force')->select('SELECT SLEEP(1.5)');
    if ($account->name === 'ordinary-quiet') $account->observer_seen = true;
});
Route::any('/health', fn () => response()->json(['ready' => true]));
Route::any('/baseline', function () {
    $m = OctaneRaceAccount::on('default')->create(['name' => 'baseline', 'is_admin' => true]);
    return response()->json(['name' => $m->name, 'is_admin' => (int) $m->is_admin]);
});
Route::any('/force', function () {
    OctaneRaceAccount::on('force')->forceCreate(['name' => 'force-request', 'is_admin' => false]);
    return response()->json(['force_complete' => true]);
});
Route::any('/ordinary', function () {
    $m = OctaneRaceAccount::on('ordinary')->create(['name' => 'ordinary-request', 'is_admin' => true]);
    return response()->json(['name' => $m->name, 'is_admin' => (int) $m->is_admin]);
});
Route::any('/quiet', function () {
    Model::withoutEvents(fn () => DB::connection('quiet')->select('SELECT SLEEP(1.5)'));
    return response()->json(['quiet_complete' => true]);
});
Route::any('/ordinary-quiet', function () {
    $m = OctaneRaceAccount::on('observer')->create(['name' => 'ordinary-quiet', 'is_admin' => false]);
    return response()->json(['name' => $m->name, 'observer_seen' => (int) $m->observer_seen]);
});
