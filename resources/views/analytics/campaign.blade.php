<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Campaign Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('campaigns.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900">
                    ← Back to Campaign
                </a>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $campaign->name }} Analytics</h1>
            </div>

            <!-- Date Range Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-700">Time Period:</label>
                        <select id="daysFilter" onchange="updateAnalytics()" class="px-3 py-2 border border-gray-300 rounded-md">
                            <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="180" {{ $days == 180 ? 'selected' : '' }}>Last 180 days</option>
                            <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last 365 days</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Clicks Over Time Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Clicks Over Time</h3>
                    <div class="h-64">
                        <canvas id="clicksChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Clicks by Country -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Countries</h3>
                        <div class="h-64">
                            <canvas id="countriesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Clicks by Device -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Device Types</h3>
                        <div class="h-64">
                            <canvas id="devicesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referrers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Referrers</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referrer</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if($directTraffic > 0)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Direct / No Referrer</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $directTraffic }}</td>
                                    </tr>
                                @endif
                                @forelse($topReferrers as $referrer)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $referrer->referrer_host }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $referrer->count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No referrer data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Link Breakdown -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tracking Links Breakdown</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($linkBreakdown as $link)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $link->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $link->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $link->clicks_count ?? 0 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('analytics.tracking-link', $link) }}" class="text-indigo-600 hover:text-indigo-900">
                                                View Analytics
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No tracking links in this campaign</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        const clicksData = @json($clicksOverTime);
        const countriesData = @json($clicksByCountry);
        const devicesData = @json($clicksByDevice);
        const topReferrersData = @json($topReferrers);
        const directTrafficCount = {{ $directTraffic }};

        // Clicks Over Time Chart
        const clicksCtx = document.getElementById('clicksChart').getContext('2d');
        new Chart(clicksCtx, {
            type: 'line',
            data: {
                labels: clicksData.map(item => item.date),
                datasets: [{
                    label: 'Clicks',
                    data: clicksData.map(item => item.count),
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Countries Chart
        const countriesCtx = document.getElementById('countriesChart').getContext('2d');
        new Chart(countriesCtx, {
            type: 'bar',
            data: {
                labels: countriesData.map(item => item.country),
                datasets: [{
                    label: 'Clicks',
                    data: countriesData.map(item => item.count),
                    backgroundColor: 'rgba(79, 70, 229, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y'
            }
        });

        // Devices Chart
        const devicesCtx = document.getElementById('devicesChart').getContext('2d');
        new Chart(devicesCtx, {
            type: 'doughnut',
            data: {
                labels: devicesData.map(item => item.device_type),
                datasets: [{
                    data: devicesData.map(item => item.count),
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        function updateAnalytics() {
            const days = document.getElementById('daysFilter').value;
            window.location.href = `{{ route('analytics.campaign', $campaign) }}?days=${days}`;
        }
    </script>
</x-app-layout>
