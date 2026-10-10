<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class PublicAccessToken
{
    public function issue(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function matches(string $token, string $storedHash): bool
    {
        return hash_equals($storedHash, $this->hash($token));
    }

    public function expiresAt(int $minutes): Carbon
    {
        return now()->addMinutes($minutes);
    }
}
