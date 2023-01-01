<?php

use App\Http\Controllers\InvitationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});
Route::group([
    'middleware' => 'api',
], function () {
    Route::post('/invitation/user/{id}', [InvitationController::class, 'create']);
    Route::delete('/invitation/user/{id}', [InvitationController::class, 'delete']);
    Route::post('/invitation/inviter/{id}', [InvitationController::class, 'acceptInvitation']);
    Route::delete('/invitation/inviter/{id}', [InvitationController::class, 'rejectInvitation']);
});
