<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Command;
use App\Actions\FetchChatRooms;
use Illuminate\Http\JsonResponse;

class FetchChatRoomsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Command::dispatch(FetchChatRooms::class);

        return response()->json('', 200);
    }
}
