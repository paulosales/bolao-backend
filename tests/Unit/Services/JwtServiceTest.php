<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\JwtService;
use PHPUnit\Framework\TestCase;

class JwtServiceTest extends TestCase
{
    private const SECRET = 'unit_test_secret_key_32bytes_min!';

    private JwtService $service;

    protected function setUp(): void
    {
        $this->service = new JwtService(self::SECRET);
    }

    // ── encode / decode round-trip ────────────────────────────────────────────

    public function testEncodeReturnsNonEmptyString(): void
    {
        $token = $this->service->encode(1);
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function testTokenHasThreeJwtParts(): void
    {
        $token = $this->service->encode(1);
        $this->assertCount(3, explode('.', $token));
    }

    public function testDecodeReturnsCorrectSubject(): void
    {
        $token = $this->service->encode(42);
        $payload = $this->service->decode($token);

        $this->assertNotNull($payload);
        $this->assertSame(42, $payload->sub);
    }

    public function testDecodeIncludesIatAndExp(): void
    {
        $before = time();
        $token  = $this->service->encode(1);
        $after  = time();

        $payload = $this->service->decode($token);
        $this->assertNotNull($payload);
        $this->assertGreaterThanOrEqual($before, $payload->iat);
        $this->assertLessThanOrEqual($after, $payload->iat);
        $this->assertGreaterThan($payload->iat, $payload->exp);
    }

    public function testExtraClaimsArePreserved(): void
    {
        $token   = $this->service->encode(7, ['role' => 'admin', 'name' => 'Paulo']);
        $payload = $this->service->decode($token);

        $this->assertNotNull($payload);
        $this->assertSame('admin', $payload->role);
        $this->assertSame('Paulo', $payload->name);
    }

    // ── invalid / tampered tokens ─────────────────────────────────────────────

    public function testDecodeReturnsNullForGibberish(): void
    {
        $this->assertNull($this->service->decode('not.a.valid.token'));
    }

    public function testDecodeReturnsNullForEmptyString(): void
    {
        $this->assertNull($this->service->decode(''));
    }

    public function testDecodeReturnsNullForWrongSecret(): void
    {
        $token      = $this->service->encode(1);
        $otherSvc   = new JwtService('completely_different_secret_32b!');

        $this->assertNull($otherSvc->decode($token));
    }

    public function testDecodeReturnsNullForTamperedPayload(): void
    {
        $token  = $this->service->encode(1);
        $parts  = explode('.', $token);
        // Corrupt the payload segment
        $parts[1] = base64_encode('{"sub":999,"iat":0,"exp":9999999999}');
        $tampered = implode('.', $parts);

        $this->assertNull($this->service->decode($tampered));
    }
}
