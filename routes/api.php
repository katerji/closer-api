<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MessageController;
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
    'middleware' => 'auth:api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [AuthController::class, 'login'])->withoutMiddleware('auth:api');
    Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware('auth:api');;
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});
Route::group([
    'middleware' => 'auth:api',
], function () {
    Route::post('/invitation', [InvitationController::class, 'create']);
    Route::get('/invitations', [InvitationController::class, 'index']);
    Route::delete('/invitation/user/{id}', [InvitationController::class, 'delete']);
    Route::post('/invitation/inviter/{id}', [InvitationController::class, 'acceptInvitation']);
    Route::delete('/invitation/inviter/{id}', [InvitationController::class, 'rejectInvitation']);

    Route::get('/contacts', [ContactController::class, 'index']);

    Route::post('/message', [MessageController::class, 'create']);
    Route::get('/messages/chat/{id}', [MessageController::class, 'index']);

    Route::post('/chat', [ChatController::class, 'create']);
    Route::get('/chat/{id}', [ChatController::class, 'getChat']);
    Route::get('/chats', [ChatController::class, 'index']);

});
