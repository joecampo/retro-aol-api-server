<?php

use App\Http\Controllers\Api\ChatMessagesController;
use App\Http\Controllers\Api\ChatUsersController;
use App\Http\Controllers\Api\SessionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoffController;
use App\Http\Controllers\Api\FetchChatRoomsController;
use App\Http\Controllers\Api\JoinChatRoomController;
use App\Http\Controllers\Api\LeaveChatRoomController;
use App\Http\Controllers\Api\SendChatMessageController;
use App\Http\Controllers\Api\SendInstantMessageController;
use App\Http\Controllers\Api\StatusController;

Route::post('/sessions', [SessionsController::class, '__invoke']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/login', LoginController::class);
    Route::post('/logoff', LogoffController::class);
    Route::get('/status', StatusController::class);
    Route::post('/join-chat-room', JoinChatRoomController::class);
    Route::post('/leave-chat-room', LeaveChatRoomController::class);
    Route::post('/send-chat-message', SendChatMessageController::class);
    Route::get('/chat-messages', ChatMessagesController::class);
    Route::get('/chat-users', ChatUsersController::class);
    Route::post('/send-instant-message', SendInstantMessageController::class);
    Route::post('/fetch-chat-rooms', FetchChatRoomsController::class);
});
