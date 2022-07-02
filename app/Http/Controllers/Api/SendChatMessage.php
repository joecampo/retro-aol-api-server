<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Actions\SendChatMessage as SendMessage;
use App\Services\Command;

class SendChatMessage extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Command::dispatch(SendMessage::class, $request->message);

        return response()->json('', 201);
    }
}
