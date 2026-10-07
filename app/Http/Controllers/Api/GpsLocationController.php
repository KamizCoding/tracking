<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GpsLocationController extends Controller
{
    public function update(Request $request)
    {
        Log::info('GPS location update received', $request->all());

        $validated = $request->validate([
            'click_event_id' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $click = Click::where('click_event_id', $validated['click_event_id'])->first();

        if (! $click) {
            Log::warning('Click not found for GPS update, will retry', ['click_event_id' => $validated['click_event_id']]);

            // Return 404 but tell frontend to retry
            return response()->json(['error' => 'Click not found', 'retry' => true], 404);
        }

        // Reverse geocode to get city/region/country
        $locationData = $this->reverseGeocode($validated['latitude'], $validated['longitude']);

        Log::info('Reverse geocoding result', $locationData);

        $click->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'country' => $locationData['country'] ?? $click->country,
            'country_code' => $locationData['country_code'] ?? $click->country_code,
            'region' => $locationData['region'] ?? $click->region,
            'city' => $locationData['city'] ?? $click->city,
            'location_source' => 'gps',
        ]);

        // Fix region name if it's a Province instead of District
        if ($click->region === 'Central Province' && $click->city === 'Kandy') {
            $click->update(['region' => 'Kandy District']);
        }

        Log::info('Click updated with GPS location', ['click_id' => $click->id]);

        return response()->json(['success' => true])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    protected function reverseGeocode(float $latitude, float $longitude): array
    {
        // Use manual city lookup for Sri Lanka (more reliable than OpenStreetMap)
        $corrected = $this->correctSriLankaCity($latitude, $longitude);
        if ($corrected) {
            return $corrected;
        }

        // Fallback to OpenStreetMap if not in Sri Lanka or no match
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
            $response = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'User-Agent' => 'LinkTracker/1.0',
                    'timeout' => 5,
                ],
            ]));

            if ($response) {
                $data = json_decode($response, true);

                if (isset($data['address'])) {
                    $address = $data['address'];

                    return [
                        'country' => $address['country'] ?? null,
                        'country_code' => $address['country_code'] ?? null,
                        'region' => $address['state'] ?? $address['state_district'] ?? $address['region'] ?? null,
                        'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['suburb'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Reverse geocoding failed', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Manual city lookup for Sri Lanka to improve accuracy
     */
    protected function correctSriLankaCity(float $lat, float $lon): ?array
    {
        // Sri Lanka major cities with approximate coordinates and districts
        $cities = [
            ['name' => 'Kandy', 'lat' => 7.2906, 'lon' => 80.6336, 'region' => 'Kandy District'],
            ['name' => 'Colombo', 'lat' => 6.9355, 'lon' => 79.8487, 'region' => 'Colombo District'],
            ['name' => 'Nuwara Eliya', 'lat' => 6.9708, 'lon' => 80.7829, 'region' => 'Nuwara Eliya District'],
            ['name' => 'Galle', 'lat' => 6.0461, 'lon' => 80.2103, 'region' => 'Galle District'],
            ['name' => 'Jaffna', 'lat' => 9.6685, 'lon' => 80.0074, 'region' => 'Jaffna District'],
            ['name' => 'Matara', 'lat' => 5.9485, 'lon' => 80.5353, 'region' => 'Matara District'],
            ['name' => 'Anuradhapura', 'lat' => 8.3122, 'lon' => 80.4131, 'region' => 'Anuradhapura District'],
            ['name' => 'Trincomalee', 'lat' => 8.5778, 'lon' => 81.2289, 'region' => 'Trincomalee District'],
            ['name' => 'Batticaloa', 'lat' => 7.7102, 'lon' => 81.6924, 'region' => 'Batticaloa District'],
            ['name' => 'Kurunegala', 'lat' => 7.4839, 'lon' => 80.3683, 'region' => 'Kurunegala District'],
        ];

        // Find the closest city within 50km
        $closest = null;
        $minDistance = 50; // 50km threshold

        foreach ($cities as $city) {
            $distance = $this->haversineDistance($lat, $lon, $city['lat'], $city['lon']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closest = $city;
            }
        }

        if ($closest) {
            return [
                'country' => 'Sri Lanka',
                'country_code' => 'LK',
                'region' => $closest['region'],
                'city' => $closest['name'],
            ];
        }

        return null;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
