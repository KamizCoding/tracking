<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_others_tracking_link(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('tracking-links.show', $link));

        $response->assertStatus(403);
    }

    public function test_user_cannot_edit_others_tracking_link(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('tracking-links.edit', $link));

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_tracking_link(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->delete(route('tracking-links.destroy', $link));

        $response->assertStatus(403);
    }

    public function test_user_cannot_view_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('campaigns.show', $campaign));

        $response->assertStatus(403);
    }

    public function test_user_cannot_edit_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('campaigns.edit', $campaign));

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->delete(route('campaigns.destroy', $campaign));

        $response->assertStatus(403);
    }

    public function test_user_cannot_create_link_with_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->post(route('tracking-links.store'), [
                'name' => 'Test Link',
                'destination_url' => 'https://example.com',
                'campaign_id' => $campaign->id,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }
}
