<?php

namespace Database\Factories;

use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackingLink>
 */
class TrackingLinkFactory extends Factory
{
    protected $model = TrackingLink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'campaign_id' => null,
            'code' => strtoupper(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(6))), 0, 6)),
            'name' => fake()->sentence(3),
            'destination_url' => fake()->url(),
            'status' => 'active',
            'click_count' => fake()->numberBetween(0, 1000),
            'expires_at' => null,
        ];
    }
}
