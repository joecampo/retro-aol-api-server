<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use App\Jobs\Client;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        if ($request->user()->online) {
            return response()->json('', 418);
        }

        $request->user()->update(['online' => true]);

        Client::dispatch($request->user(), [$request->username, $request->password]);

        return response()->json('', 201);
    }
}
