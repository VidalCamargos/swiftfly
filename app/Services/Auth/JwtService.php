<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class JwtService
{
    public function authenticate(User $user): array
    {
        $token = Auth::setTTL(config('jwt.ttl'))
            ->claims(['user_id' => $user->id])
            ->login($user);
        $payload = Auth::setToken($token)->getPayload();

        return [
            'token' => $token,
            'jti' => $payload->get('jti'),
            'expires_at' => $payload->get('exp'),
            'type' => 'bearer',
        ];
    }
}
