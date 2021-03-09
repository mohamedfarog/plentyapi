<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\CatController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PassController;
use App\Http\Controllers\ProdcatController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Thenextweb\Definitions\StoreCard;
use Thenextweb\PassGenerator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('otp', OtpController::class);
Route::post('verify', [OtpController::class, 'verify']);
Route::post('register', [UserController::class, 'register']);
Route::get('checkinvitation', [AccessController::class, 'checkList']);
Route::resource('categories', CatController::class);
Route::resource('prodcat', ProdcatController::class);
Route::resource('products', ProductController::class);
Route::post('checkstock', [SizeController::class, 'checkStock']);
Route::get('otpnum', [OtpController::class, 'otpNumber']);
Route::get('invnum', [AccessController::class, 'accessNumber']);
Route::get('getwa', [SupportController::class, 'sendWhatsapp']);
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('profile', [UserController::class, 'myProfile']);
    Route::post('autologin', [UserController::class, 'autologin']);
    Route::post('invitation', [AccessController::class, 'invite']);
    Route::post('shops', [ShopController::class, 'store']);
    Route::get('invstatus', [AccessController::class, 'checkAccess']);
    Route::resource('support', SupportController::class);
    Route::resource('orders', OrderController::class);
});

Route::get('generate', function(Request $request){
    $len = 24;
    if(isset($request->length)){
        $len = $request->length;
    }

    return Str::random($len);
});

Route::prefix('v1')->group(function () {
    Route::post('/devices/{deviceLibraryIdentifier}/registrations/{passTypeIdentifier}/{serialNumber}', [PassController::class, 'registerDevice'])->middleware('passed');
    Route::get('/devices/{deviceLibraryIdentifier}/registrations/{passTypeIdentifier}',[PassController::class, 'getPasses']);
    Route::get('/passes/{passTypeIdentifier}/{serialNumber}',[PassController::class, 'getPass'])->middleware('passed');
    Route::delete('/devices/{deviceLibraryIdentifier}/registrations/{passTypeIdentifier}/{serialNumber}', [PassController::class, 'deletePass'])->middleware('passed');
    Route::post('/log', [PassController::class, 'logIt']);
});


Route::post('hello', function (Request $request) {
    // Mail::send('support', [
    //     'shop_email' => "riveraeric19@gmail.com", 'shopname_en' => "Eric", 'shopname_ar' => "Eric", 'shop_tel' => "0566419450", 'type' => "Bug", 'body' => "Hello"
    // ], function ($m) {
    //     $m->from("riveraeric19@gmail.com", 'Plenty Support Request');

    //     $m->to('noreply@plentyapp.mvp-apps.ae')->subject('Plenty Support Request');
    // });
    $pkpass = PassGenerator::getPass('P-108428-091492-71.pkpass');
    return $pkpass;
});
