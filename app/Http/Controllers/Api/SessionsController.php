<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSessionRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Session;

class SessionsController extends Controller
{
    public function __invoke(CreateSessionRequest $request): JsonResponse
    {
        return with($request->findOrCreateSession(), function (Session $session) use ($request) {
            return response()->json([
                'online' => $session->online,
                'sessionId' => $session->uuid,
                'token' => $request->token,
            ], 201);
        });
    }
}
