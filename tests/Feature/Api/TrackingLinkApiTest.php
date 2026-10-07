<?php

namespace Tests\Feature\Api;

use App\Models\BlockedDomain;
use App\Models\Campaign;
use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrackingLinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/v1/tracking-links');

        $response->assertStatus(401);
    }

    public function test_user_can_list_their_tracking_links(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        TrackingLink::factory()->count(3)->create(['user_id' => $user->id]);
        TrackingLink::factory()->count(2)->create(); // Other user's links

        $response = $this->getJson('/api/v1/tracking-links');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_tracking_link(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tracking-links', [
            'name' => 'Test Link',
            'destination_url' => 'https://example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Link')
            ->assertJsonPath('data.destination_url', 'https://example.com');

        $this->assertDatabaseHas('tracking_links', [
            'user_id' => $user->id,
            'name' => 'Test Link',
        ]);
    }

    public function test_user_cannot_create_link_with_invalid_url(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tracking-links', [
            'name' => 'Test Link',
            'destination_url' => 'javascript:alert(1)',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_create_link_with_blocked_domain(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BlockedDomain::create(['domain' => 'malicious.com', 'reason' => 'Test']);

        $response = $this->postJson('/api/v1/tracking-links', [
            'name' => 'Test Link',
            'destination_url' => 'https://malicious.com',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_create_link_with_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->postJson('/api/v1/tracking-links', [
            'name' => 'Test Link',
            'destination_url' => 'https://example.com',
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(400);
    }

    public function test_user_can_view_their_tracking_link(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $link = TrackingLink::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/tracking-links/{$link->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $link->id)
            ->assertJsonPath('data.name', $link->name);
    }

    public function test_user_cannot_view_others_tracking_link(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/tracking-links/{$link->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_their_tracking_link(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $link = TrackingLink::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/tracking-links/{$link->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('tracking_links', [
            'id' => $link->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_others_tracking_link(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->putJson("/api/v1/tracking-links/{$link->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_tracking_link(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $link = TrackingLink::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/tracking-links/{$link->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('tracking_links', [
            'id' => $link->id,
        ]);
    }

    public function test_user_cannot_delete_others_tracking_link(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/v1/tracking-links/{$link->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_filter_links_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        TrackingLink::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        TrackingLink::factory()->create(['user_id' => $user->id, 'status' => 'disabled']);

        $response = $this->getJson('/api/v1/tracking-links?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_links_by_campaign(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        TrackingLink::factory()->create(['user_id' => $user->id, 'campaign_id' => $campaign->id]);
        TrackingLink::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/tracking-links?campaign_id={$campaign->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
