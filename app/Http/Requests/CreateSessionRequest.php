<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Session;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

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
        if ($session = $this->currentSession()) {
            return $session;
        }

        return Session::create(['identity_id' => Hash::make($this->ip()), 'uuid' => str()->uuid()]);
    }

    public function token(Session $session): string
    {
        return $this->bearerToken() ?? $session->createToken(str()->uuid())->plainTextToken;
    }

    private function currentSession(): ?Session
    {
        return with(
            PersonalAccessToken::findToken($this->bearerToken()),
            function (?PersonalAccessToken $token) {
                return $token ? $token->tokenable : null;
            }
        );
    }
}
