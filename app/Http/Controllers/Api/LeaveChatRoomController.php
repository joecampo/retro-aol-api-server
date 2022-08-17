<?php

namespace App\Http\Controllers\Api;

use App\Actions\LeaveChatRoom;
use App\Http\Controllers\Controller;
use App\Services\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveChatRoomController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Command::dispatch(LeaveChatRoom::class, $request->roomName);

        return response()->json('', 200);
    }
}
