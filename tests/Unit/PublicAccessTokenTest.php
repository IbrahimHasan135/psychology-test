<?php

namespace Tests\Unit;

use App\Support\PublicAccessToken;
use Tests\TestCase;

class PublicAccessTokenTest extends TestCase
{
    public function test_tokens_are_random_and_only_the_original_token_matches(): void
    {
        $service = app(PublicAccessToken::class);
        $first = $service->issue();
        $second = $service->issue();

        $this->assertNotSame($first, $second);
        $this->assertTrue($service->matches($first, $service->hash($first)));
        $this->assertFalse($service->matches($second, $service->hash($first)));
        $this->assertSame(64, strlen($first));
    }
}
