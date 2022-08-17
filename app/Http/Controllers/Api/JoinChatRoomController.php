<?php

namespace App\Http\Controllers\Api;

use App\Actions\JoinChatRoom;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\Command;
use Illuminate\Http\Request;

class JoinChatRoomController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Command::dispatch(JoinChatRoom::class, $request->roomName);

        return response()->json('', 200);
    }
}
