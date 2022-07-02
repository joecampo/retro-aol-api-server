<?php

namespace App\Listeners;

use App\Models\Session;
use Laravel\Sanctum\PersonalAccessToken;

class ClearSessions
{
    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Session::truncate();
        PersonalAccessToken::truncate();
    }
}
