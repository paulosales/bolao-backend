<?php
declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class JwtService
{
    private string $algo = 'HS256';

    public function __construct(private string $secret)
    {
    }

    public function encode(int $userId, array $extra = []): string
    {
        $expiry = (int) ($_ENV['JWT_EXPIRY'] ?? 86400);
        $payload = array_merge([
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + $expiry,
        ], $extra);
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function decode(string $token): ?stdClass
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (\Throwable) {
            return null;
        }
    }
}
