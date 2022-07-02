<?php

use App\Http\Controllers\Api\SessionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoffController;
use App\Http\Controllers\Api\JoinChatController;
use App\Http\Controllers\Api\SendChatMessage;

Route::post('/sessions', [SessionsController::class, '__invoke']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/login', [LoginController::class, '__invoke']);
    Route::post('/logoff', [LogoffController::class, '__invoke']);
    Route::post('/join-chat', [JoinChatController::class, '__invoke']);
    Route::post('/send-chat-message', [SendChatMessage::class, '__invoke']);
});
