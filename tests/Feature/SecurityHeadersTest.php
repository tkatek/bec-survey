<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_browser_security_headers_are_applied_to_html_and_health_responses(): void
    {
        foreach (['/login', '/up'] as $path) {
            $this->get($path)
                ->assertSuccessful()
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
        }
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get('/login')
            ->assertSuccessful()
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/login')
            ->assertSuccessful()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}
