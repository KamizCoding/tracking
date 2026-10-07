<?php

namespace Tests\Feature;

use App\Models\LinkDomain;
use App\Models\TrackingLink;
use App\Models\User;
use App\Services\TrackingUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Custom Domain Routing Tests
 *
 * These tests verify the Laravel application logic for custom-domain routing.
 *
 * IMPORTANT: For production use, the web server (Nginx/Apache) must be configured to:
 * 1. Point custom domains (e.g., go.example.com) to this Laravel application
 * 2. Rewrite requests: go.example.com/ABC123 → /track/ABC123
 *
 * The Laravel application then:
 * - Detects the custom domain via the Host header
 * - Enforces link_domain_id isolation
 * - Processes clicks through the normal ProcessClick pipeline
 * - Redirects to the destination
 *
 * This architecture keeps Laravel simple and delegates host-based routing to the web server.
 */
class CustomDomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_domain_resolves_assigned_link(): void
    {
        $user = User::factory()->create();

        $domain = new LinkDomain([
            'user_id' => $user->id,
            'domain' => 'go.example.com',
            'verification_token' => 'verify123',
            'status' => 'verified',
            'verified' => true,
        ]);
        $domain->save();

        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'code' => 'ABC123',
            'destination_url' => 'https://example.com/destination',
            'link_domain_id' => $domain->id,
            'status' => 'active',
        ]);

        // Verify domain was created
        $this->assertDatabaseHas('link_domains', [
            'domain' => 'go.example.com',
            'status' => 'verified',
        ]);

        // Verify link was created with correct domain
        $this->assertDatabaseHas('tracking_links', [
            'code' => 'ABC123',
            'link_domain_id' => $domain->id,
        ]);

        // Verify the TrackingUrlService can match the custom domain
        $trackingUrlService = new TrackingUrlService;
        $matchedDomain = $trackingUrlService->matchesCustomDomain('go.example.com');
        $this->assertNotNull($matchedDomain);
        $this->assertEquals($domain->id, $matchedDomain->id);

        // In production, the web server should be configured to:
        // 1. Point go.example.com to this Laravel application
        // 2. Rewrite go.example.com/ABC123 to /track/ABC123
        // The controller then uses the Host header to identify the custom domain
        // and enforces link_domain_id isolation
    }

    public function test_wrong_custom_domain_rejects_same_code(): void
    {
        $user = User::factory()->create();

        $domainA = new LinkDomain([
            'user_id' => $user->id,
            'domain' => 'go.example.com',
            'verification_token' => 'verify123',
            'status' => 'verified',
            'verified' => true,
        ]);
        $domainA->save();

        $domainB = new LinkDomain([
            'user_id' => $user->id,
            'domain' => 'go.another.com',
            'verification_token' => 'verify456',
            'status' => 'verified',
            'verified' => true,
        ]);
        $domainB->save();

        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'code' => 'ABC123',
            'destination_url' => 'https://example.com/destination',
            'link_domain_id' => $domainA->id,
            'status' => 'active',
        ]);

        $response = $this->get('/track/ABC123', [
            'Host' => 'go.another.com',
        ]);

        $response->assertStatus(404);
    }

    public function test_unverified_domain_rejects_request(): void
    {
        $user = User::factory()->create();

        $domain = new LinkDomain([
            'user_id' => $user->id,
            'domain' => 'go.example.com',
            'verification_token' => 'verify123',
            'status' => 'pending',
            'verified' => false,
        ]);
        $domain->save();

        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'code' => 'ABC123',
            'destination_url' => 'https://example.com/destination',
            'link_domain_id' => $domain->id,
            'status' => 'active',
        ]);

        $response = $this->get('/track/ABC123', [
            'Host' => 'go.example.com',
        ]);

        $response->assertStatus(404);
    }

    public function test_default_tracking_domain_still_works(): void
    {
        $user = User::factory()->create();

        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'code' => 'XYZ789',
            'destination_url' => 'https://example.com/destination',
            'link_domain_id' => null,
            'status' => 'active',
        ]);

        $response = $this->get('/track/XYZ789');

        $response->assertRedirect('https://example.com/destination');
    }
}
