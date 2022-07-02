<?php

namespace App\Http\Controllers\Api;

use App\Actions\JoinChat;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\Command;

class JoinChatController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Command::dispatch(JoinChat::class, 'Welcome');

        return response()->json('', 200);
    }
}
