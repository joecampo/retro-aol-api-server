<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Session;
use Illuminate\Support\Facades\Hash;

class CreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }

    public function findOrCreateSession(): Session
    {
        if ($this->currentSession()) {
            return $this->currentSession();
        }

        return Session::create(['identity_id' => Hash::make($this->ip()), 'uuid' => str()->uuid()]);
    }

    private function currentSession(): ?Session
    {
        return once(function () {
            return Session::all()->first(fn (Session $session) => Hash::check($this->ip(), $session->identity_id));
        });
    }
}
