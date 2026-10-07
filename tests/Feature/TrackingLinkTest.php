<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_tracking_link(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('tracking-links.store'), [
                'name' => 'Test Link',
                'destination_url' => 'https://example.com',
                'campaign_id' => $campaign->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tracking_links', [
            'name' => 'Test Link',
            'destination_url' => 'https://example.com',
        ]);
    }

    public function test_tracking_redirect_works_for_active_link(): void
    {
        $user = User::factory()->create();
        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'destination_url' => 'https://example.com',
        ]);

        $response = $this->get(route('tracking.redirect', $link->code));

        $response->assertRedirect('https://example.com');
    }

    public function test_tracking_redirect_fails_for_disabled_link(): void
    {
        $user = User::factory()->create();
        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'status' => 'disabled',
        ]);

        $response = $this->get(route('tracking.redirect', $link->code));

        $response->assertStatus(403);
    }

    public function test_tracking_redirect_fails_for_expired_link(): void
    {
        $user = User::factory()->create();
        $link = TrackingLink::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('tracking.redirect', $link->code));

        $response->assertStatus(410);
    }

    public function test_tracking_redirect_fails_for_nonexistent_link(): void
    {
        $response = $this->get(route('tracking.redirect', 'NONEXIST'));

        $response->assertStatus(404);
    }

    public function test_user_can_only_view_their_own_tracking_links(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('tracking-links.show', $link->id));

        $response->assertStatus(403);
    }
}
