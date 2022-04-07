<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

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

Route::middleware('api')->prefix('v1')->group(function(){
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('refresh', [UserController::class, 'refresh']);
    Route::post('profile', [UserController::class, 'profile']);

    //User
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'getAllUsers']);
        Route::post('get-details', [UserController::class, 'getUserDetails']);
    });

    //Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/', [UserController::class, 'getAllWallets']);
        Route::post('get-details', [UserController::class, 'showWalletDetails']);
    });
});
