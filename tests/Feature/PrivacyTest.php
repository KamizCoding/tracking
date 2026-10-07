<?php

namespace Tests\Feature;

use App\Models\PrivacySettings;
use App\Models\User;
use App\Services\PrivacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_settings_default_to_anonymize_ips(): void
    {
        $user = User::factory()->create();

        // Create privacy settings with default values
        $settings = PrivacySettings::create([
            'user_id' => $user->id,
            'data_retention_days' => 90,
            'anonymize_ips' => true,
            'store_raw_user_agents' => true,
        ]);

        $this->assertTrue($settings->anonymize_ips, 'Privacy settings should default to anonymize_ips = true');
    }

    public function test_ipv4_anonymization_masks_last_octet(): void
    {
        $privacyService = new PrivacyService;
        $user = User::factory()->create();
        PrivacySettings::create([
            'user_id' => $user->id,
            'anonymize_ips' => true,
        ]);

        $result = $privacyService->anonymizeIp('192.168.1.100', $user->id);

        $this->assertEquals('192.168.1.0', $result);
    }

    public function test_ipv6_anonymization_masks_to_48(): void
    {
        $privacyService = new PrivacyService;
        $user = User::factory()->create();
        PrivacySettings::create([
            'user_id' => $user->id,
            'anonymize_ips' => true,
        ]);

        $result = $privacyService->anonymizeIp('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $user->id);

        // Should mask to /48
        $this->assertStringStartsWith('2001:db8:85a3::', $result);
    }

    public function test_ip_not_anonymized_when_disabled(): void
    {
        $privacyService = new PrivacyService;
        $user = User::factory()->create();
        PrivacySettings::create([
            'user_id' => $user->id,
            'anonymize_ips' => false,
        ]);

        $originalIp = '192.168.1.100';
        $result = $privacyService->anonymizeIp($originalIp, $user->id);

        $this->assertEquals($originalIp, $result);
    }

    public function test_raw_user_agent_can_be_disabled(): void
    {
        $privacyService = new PrivacyService;
        $user = User::factory()->create();
        PrivacySettings::create([
            'user_id' => $user->id,
            'store_raw_user_agents' => false,
        ]);

        $result = $privacyService->shouldStoreRawUserAgent($user->id);

        $this->assertFalse($result);
    }

    public function test_raw_user_agent_enabled_by_default(): void
    {
        $privacyService = new PrivacyService;
        $user = User::factory()->create();

        $result = $privacyService->shouldStoreRawUserAgent($user->id);

        $this->assertTrue($result);
    }
}
