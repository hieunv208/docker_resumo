<?php

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteRegistrar;
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

//Protected route
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/login_email',[LoginController::class, 'postCode']);
});

Route::post('/login_request_email', [LoginController::class, 'index']);
Route::post('/register', [LoginController::class, 'store']);



Route::get('/sms/send/', function(){
    try {

        $basic  = new \Nexmo\Client\Credentials\Basic(getenv("NEXMO_KEY"), getenv("NEXMO_SECRET"));
        $client = new \Nexmo\Client($basic);

        $receiverNumber = "84335141096";
        $message = "This is testing from Leon RESUMO";

        $message = $client->message()->send([
            'to' => $receiverNumber,
            'from' => 'Vonage APIs',
            'text' => $message
        ]);
        Log::info('sent message: ' . $message['message-id']);
        dd('SMS Sent Successfully.');


    } catch (Exception $e) {
        dd("Error: ". $e->getMessage());
    }

});