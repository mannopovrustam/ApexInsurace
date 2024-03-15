<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewController;
use App\Models\Contract\ContractPayment;
use App\Repository\DataRepository;
use App\Repository\ReportRepository;
use App\Services\DicService;
use App\Services\FakturaService;
use App\Services\RegisterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
//use Buki\AutoRoute\AutoRouteFacade as Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('dashboard');
});


Route::group(['middleware' => ['auth']], function() {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});

Route::get('/dashboard', function () {

    $currentMonth = date('Y-m-d');

    if(isset(request()->currentMonth)) $currentMonth = request()->currentMonth;

    $data['report1a'] = collect(DB::select('select count(*) as val from contracts'))->first()->val;
    $data['report1b'] = collect(DB::select('select sum(amount+IFNULL(tax,0)+IFNULL(expense,0)) as val from contracts'))->first()->val;

    $data['report2a'] = collect(DB::select('select count(*) as val from contracts where status = 10'))->first()->val;
    $data['report2b'] = collect(DB::select('select sum(if(amount_paid > (amount+IFNULL(tax,0)+IFNULL(expense,0)), (amount+IFNULL(tax,0)+IFNULL(expense,0)), amount_paid)) as val from contracts'))->first()->val;

    $data['report3a'] = collect(DB::select('select count(*) as val from contracts where status != 10 and status != 6'))->first()->val;
    $data['report3b'] = collect(DB::select('select sum(amount+IFNULL(tax,0)+IFNULL(expense,0)) - sum(amount_paid) as val from contracts where status != 10 or status != 6'))->first()->val;

    $data['report4a'] = collect(DB::select("select count(*) as val from contracts where MONTH(closed_at) = MONTH('$currentMonth') and YEAR(closed_at) = YEAR('$currentMonth') and status = 10"))->first()->val;
//    dd("select count(*) as val from contracts where MONTH(closed_at) = MONTH('$currentMonth') and YEAR(closed_at) = YEAR('$currentMonth') and status = 10");
    $data['report4b'] = collect(DB::select("select sum(cp.amount) as val from contract_payments as cp join contracts as c on cp.contract_id = c.id where MONTH(cp.date) = MONTH('$currentMonth') and YEAR(cp.date) = YEAR('$currentMonth') and cp.cancelled = 0"))->first()->val;

    $data['report5a'] = collect(DB::select("select count(*) as val from contracts where MONTH(created_at) = MONTH('$currentMonth') and YEAR(created_at) = YEAR('$currentMonth')"))->first()->val;
    $data['report5b'] = collect(DB::select("select sum(amount_paid) as val from contracts where MONTH(created_at) = MONTH('$currentMonth') and YEAR(created_at) = YEAR('$currentMonth')"))->first()->val;

    $data['report6a'] = collect(DB::select("select count(*) as val from contracts where DATE(closed_at) = '$currentMonth' and status = 10"))->first()->val;
    $data['report6b'] = collect(DB::select("select sum(amount_paid) as val from contracts where DATE(closed_at) = '$currentMonth' and status = 10"))->first()->val;

    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::auto('contracts', ContractController::class);
Route::auto('petitions', PetitionController::class);

Route::resources([
    'clients' => ClientController::class,
    'contracts' => ContractController::class,
    'petitions' => PetitionController::class,
]);

Route::auto('data', DataRepository::class);
Route::auto('dic', DicService::class);
Route::auto('report', ReportRepository::class);

require __DIR__.'/auth.php';

Route::get('getsms', function (){
//    return \App\Services\SMSService::sendSMS('998977820809', 'Salom!');
});

Route::get('rewriteregions', function (){

    // truncate tables


    $region = (new FakturaService())->getRegions();
    if ($region){
	 DB::table('regions')->truncate();
	 DB::table('districts')->truncate();
    }

    $region = json_decode($region, true);

    foreach ($region['Data'] as $r) {
        DB::table('regions')->insert([
            'id' => $r['Id'],
            'name' => $r['Name'],
        ]);

        $districts = (new FakturaService())->getRegionsResponse($r['Id']);
        $districts = json_decode($districts, true);

        foreach ($districts['Data'] as $d) {
            DB::table('districts')->insert([
                'id' => $d['Id'],
                'name' => $d['Name'],
                'region_id' => $r['Id'],
            ]);
        }
    }

    return response()->json(['success' => true]);
})->middleware('auth');

Route::post('datapayment-emit', function (){
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) return response()->json(['status' => 'error', 'message' =>'Username or password is wrong!', 'data' => null]);
    if ($_SERVER['PHP_AUTH_USER'] != env('EMIT_LOGIN','emitlgn') || $_SERVER['PHP_AUTH_PW'] != env('EMIT_PSW','bsd-ryd-bdf-342')) return response()->json(['status' => 'error', 'message' =>'Username or password is wrong!', 'data' => null]);

    $value = request()->all();
    \Log::debug('emit-push: Payload -> ' . json_encode($value, JSON_UNESCAPED_UNICODE));

    \App\Jobs\paymentPush::dispatch($value);

    return response()->json(['result' => ['success'=>true]], JSON_UNESCAPED_UNICODE);

});


Route::post('datapayment-uniaccess', function (){
    $value = request()->all();
    \Log::debug('UNIACCESS-push: Payload -> ' . json_encode($value, JSON_UNESCAPED_UNICODE));
    \App\Jobs\paymentPushUNI::dispatch($value);

    return response()->json(['result' => ['success'=>true]], JSON_UNESCAPED_UNICODE);
});


Route::get('test-date', function(){
    $curdate = now()->translatedFormat('d-F') . ' ' . now()->format('Y') . ' yil ';
    return $curdate;
});


Route::get('test', function() {

        $contracts = \DB::table('contracts')
            ->select('clients.fullname', 'clients.pinfl', 'clients.passport', 'clients.phone', 'contracts.contract_name', 'contracts.id', 'contracts.client_id', 'contracts.auto_pay_activate', 'contracts.auto_pay',
                \DB::raw('(contracts.amount + contracts.tax + contracts.expense - ifnull(sum(contract_payments.amount), 0)) as residue', 'auto_pay'))
            ->join('clients', 'contracts.client_id', '=', 'clients.id')
            ->leftJoin('contract_payments', function($join){
                $join->on('contracts.id', '=', 'contract_payments.contract_id')->where('contract_payments.cancelled', '!=', '1');
            })->whereNotNULL('contracts.judge_number')
            ->where('contracts.auto_pay_activate', 1)
            ->groupBy('contracts.id')
		->limit(5)
            ->get();

	dd($contracts);

});


Route::get('test-2', function() {

    $cons = \DB::table('cons')->get();
    foreach ($cons as $key=>$con) {
	\DB::table('cons')->where('contract_id',$con->contract_id)->update(['id' => $key+1]);
    }

});

