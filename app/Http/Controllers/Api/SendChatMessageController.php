<?php

namespace App\Http\Controllers\Api;

use App\Actions\SendChatMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Command;

class SendChatMessageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Command::dispatch(SendChatMessage::class, $request->message);

        return response()->json('', 201);
    }
}
