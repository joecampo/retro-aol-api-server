<?php

namespace App\Http\Controllers\Api;

use App\Actions\LeaveChatRoom;
use App\Http\Controllers\Controller;
use App\Services\Command;
use Illuminate\Http\JsonResponse;

class LeaveChatRoomController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Command::dispatch(LeaveChatRoom::class, 'Welcome');

        return response()->json('', 200);
    }
}
