<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class StatusController extends Controller
{
    public function __invoke(): Response
    {
        return response(request()->user()->online);
    }
}
