<?php

namespace Tests\Feature\Api;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CampaignApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(401);
    }

    public function test_user_can_list_their_campaigns(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Campaign::factory()->count(3)->create(['user_id' => $user->id]);
        Campaign::factory()->count(2)->create(); // Other user's campaigns

        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_campaign(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/campaigns', [
            'name' => 'Test Campaign',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Campaign')
            ->assertJsonPath('data.description', 'Test description');

        $this->assertDatabaseHas('campaigns', [
            'user_id' => $user->id,
            'name' => 'Test Campaign',
        ]);
    }

    public function test_user_can_view_their_campaign(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/campaigns/{$campaign->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $campaign->id)
            ->assertJsonPath('data.name', $campaign->name);
    }

    public function test_user_cannot_view_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/campaigns/{$campaign->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_their_campaign(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/campaigns/{$campaign->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->putJson("/api/v1/campaigns/{$campaign->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_campaign(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/campaigns/{$campaign->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function test_user_cannot_delete_others_campaign(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/v1/campaigns/{$campaign->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_filter_campaigns_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Campaign::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Campaign::factory()->create(['user_id' => $user->id, 'status' => 'disabled']);

        $response = $this->getJson('/api/v1/campaigns?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
