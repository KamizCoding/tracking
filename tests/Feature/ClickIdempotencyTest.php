<?php

namespace Tests\Feature;

use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_clicks_in_same_second_create_separate_records(): void
    {
        $user = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user->id]);

        // Simulate two clicks from the same visitor within the same second
        $visitorId = 'test-visitor-123';

        $response1 = $this->withCookie('visitor_id', $visitorId)
            ->get(route('tracking.redirect', $link->code));

        $response2 = $this->withCookie('visitor_id', $visitorId)
            ->get(route('tracking.redirect', $link->code));

        // Both should redirect successfully
        $response1->assertStatus(302);
        $response2->assertStatus(302);

        // Should have 2 distinct click records (after queue processes)
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        $clickCount = $link->clicks()->count();
        $this->assertEquals(2, $clickCount, 'Two clicks in same second should create separate records');
    }

    public function test_click_event_ids_are_unique(): void
    {
        $user = User::factory()->create();
        $link = TrackingLink::factory()->create(['user_id' => $user->id]);

        $response1 = $this->get(route('tracking.redirect', $link->code));
        $response2 = $this->get(route('tracking.redirect', $link->code));

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        $clicks = $link->clicks()->get();
        $eventIds = $clicks->pluck('click_event_id')->toArray();

        // All click_event_ids should be unique
        $this->assertEquals(count($eventIds), count(array_unique($eventIds)), 'Click event IDs must be unique');
    }
}
