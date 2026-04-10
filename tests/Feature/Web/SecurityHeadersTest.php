<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_pages_emit_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
    }
}
