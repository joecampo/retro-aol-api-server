<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Actions\Logoff;
use App\Services\Command;

class LogoffController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Command::dispatch(Logoff::class);

        return response()->json('', 200);
    }
}
