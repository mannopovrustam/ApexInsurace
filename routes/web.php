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
use App\Services\DicService;
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
    if (!auth()->check()) Auth::loginUsingId(1);
    return view('welcome');
});


Route::group(['middleware' => ['auth']], function() {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});

Route::get('/dashboard', function () {

    $data['report1a'] = collect(\DB::select('select count(*) as val from contracts'))->first()->val;
    $data['report1b'] = collect(\DB::select('select sum(amount) as val from contracts'))->first()->val;

    $data['report2a'] = collect(\DB::select('select count(*) as val from contracts where status = 10'))->first()->val;
    $data['report2b'] = collect(\DB::select('select sum(amount) as val from contract_payments'))->first()->val;

    $data['report3a'] = collect(\DB::select('select count(*) as val from contracts where status != 10 and status != 6'))->first()->val;
    $data['report3b'] = collect(\DB::select('select sum(amount) - sum(amount_paid) as val from contracts where status != 10 or status != 6'))->first()->val;

    $data['report4a'] = collect(\DB::select('select count(*) as val from contracts where MONTH(closed_at) = MONTH(CURDATE()) and status = 10'))->first()->val;
    $data['report4b'] = collect(\DB::select('select sum(amount) as val from contract_payments where MONTH(created_at) = MONTH(CURDATE())'))->first()->val;

    $data['report5a'] = collect(\DB::select('select count(*) as val from contracts where MONTH(created_at) = MONTH(CURDATE())'))->first()->val;
    $data['report5b'] = collect(\DB::select('select sum(amount_paid) as val from contracts where MONTH(created_at) = MONTH(CURDATE())'))->first()->val;

    $data['report6a'] = collect(\DB::select('select count(*) as val from contracts where DATE(closed_at) = CURDATE() and status = 10'))->first()->val;
    $data['report6b'] = collect(\DB::select('select sum(amount_paid) as val from contracts where DATE(closed_at) = CURDATE() and status = 10'))->first()->val;

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

require __DIR__.'/auth.php';

Route::get('getsms', function (){
//    return \App\Services\SMSService::sendSMS('998977820809', 'Salom!');
});

Route::get('rewriteregions', function (){

    // truncate tables
    \DB::table('regions')->truncate();
    \DB::table('districts')->truncate();

    $region =  (new \App\Services\FakturaService())->getRegions();
    $region = json_decode($region, true);
    foreach ($region['Data'] as $r) {
        \DB::table('regions')->insert([
            'id' => $r['Id'],
            'name' => $r['Name'],
        ]);

        $districts =  (new \App\Services\FakturaService())->getRegionsResponse($r['Id']);
        $districts = json_decode($districts, true);

        foreach ($districts['Data'] as $d) {
            \DB::table('districts')->insert([
                'id' => $d['Id'],
                'name' => $d['Name'],
                'region_id' => $r['Id'],
            ]);
        }
    }

    return response()->json(['success' => true]);
})->middleware('auth');
