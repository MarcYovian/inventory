<?php

namespace Tests\Traits;

use App\Models\User;

trait WithAuthentication
{
    protected User $authenticatedUser;
    protected string $authToken;

    protected function authenticateUser(?User $user = null): void
    {
        $this->authenticatedUser = $user ?? User::factory()->create();
        $this->authToken = $this->authenticatedUser->createToken('test-token')->plainTextToken;
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->authToken,
            'Accept' => 'application/json',
        ];
    }
}
