<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\Command;
use Illuminate\Http\Request;
use App\Actions\SendInstantMessage;

class SendInstantMessageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Command::dispatch(SendInstantMessage::class, [$request->screenName, $request->message]);

        return response()->json('', 201);
    }
}
