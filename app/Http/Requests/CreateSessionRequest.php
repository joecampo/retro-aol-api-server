<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Session;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class CreateSessionRequest extends FormRequest
{
    public string $token;

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
            $this->token = $this->bearerToken();

            return $session;
        }

        return tap(
            Session::create(['identity_id' => Hash::make($this->ip()), 'uuid' => str()->uuid()]),
            function (Session $session) {
                $this->token = $session->createToken(str()->uuid())->plainTextToken;
            }
        );
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
