<?php

namespace App\Http\Controllers\Api;

use App\Actions\JoinChatRoom;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\Command;

class JoinChatRoomController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Command::dispatch(JoinChatRoom::class, 'Welcome');

        return response()->json('', 200);
    }
}
