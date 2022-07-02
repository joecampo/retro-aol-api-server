<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Jobs\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->user()->online) {
            return response()->json('', 418);
        }

        $request->user()->update(['online' => true]);

        Client::dispatch($request->user());

        return response()->json('', 201);
    }
}
