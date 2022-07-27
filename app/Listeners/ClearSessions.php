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
        Session::all()->each(function (Session $session): void {
            cache()->tags($session->id)->flush();
        });

        Session::truncate();
        PersonalAccessToken::truncate();
    }
}
