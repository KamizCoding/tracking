<?php

namespace App\Http\Controllers;

use App\Models\PrivacySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivacyController extends Controller
{
    public function index()
    {
        $settings = PrivacySettings::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'data_retention_days' => 90,
                'anonymize_ips' => true,
                'store_raw_user_agents' => true,
            ]
        );

        return view('privacy.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = PrivacySettings::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'data_retention_days' => 90,
                'anonymize_ips' => true,
                'store_raw_user_agents' => true,
            ]
        );

        $validated = $request->validate([
            'data_retention_days' => 'required|integer|min:7|max:365',
            'anonymize_ips' => 'required|boolean',
            'store_raw_user_agents' => 'required|boolean',
        ]);

        $settings->update($validated);

        return redirect()->route('privacy.index')
            ->with('success', 'Privacy settings updated successfully!');
    }
}
