<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UpdateProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('/demo', function () {
    return phpinfo();
});

//Protected route
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::put('/update-email', [UpdateProfileController::class, 'updateMail']);
    Route::put('/update-mobile', [UpdateProfileController::class, 'updatePhone']);

});
Route::post('/support', [SupportController::class, 'postSupport']);
Route::post('/login_email',[LoginController::class, 'postCodeEmail']);
Route::post('/login_request_email', [LoginController::class, 'emailLogin']);
Route::post('/login_request_mobile', [LoginController::class, 'phoneLogin']);
Route::post('/login_phone',[LoginController::class, 'postCodePhone']);
Route::post('/register', [LoginController::class, 'store']);