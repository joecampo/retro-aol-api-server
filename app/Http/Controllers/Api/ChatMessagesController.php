<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ChatMessagesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(cache()->tags(request()->user()->id)->get('chat_messages', collect()));
    }
}
