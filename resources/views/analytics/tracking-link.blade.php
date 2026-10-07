<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Link Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('tracking-links.show', $trackingLink) }}" class="text-indigo-600 hover:text-indigo-900">
                    ← Back to Link
                </a>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $trackingLink->name }} Analytics</h1>
            </div>

            <!-- Date Range Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center gap-4 flex-wrap">
                        <label class="text-sm font-medium text-gray-700">Time Period:</label>
                        <select id="daysFilter" onchange="updateAnalytics()" class="px-3 py-2 border border-gray-300 rounded-md">
                            <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="180" {{ $days == 180 ? 'selected' : '' }}>Last 180 days</option>
                            <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last 365 days</option>
                        </select>

                        <div class="border-l border-gray-300 h-8 mx-2"></div>

                        <label class="text-sm font-medium text-gray-700">Specific Click:</label>
                        <select id="clickFilter" onchange="updateAnalytics()" class="px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">All Clicks</option>
                            @foreach($allClicks as $click)
                                <option value="{{ $click->id }}" {{ $selectedClickId == $click->id ? 'selected' : '' }}>
                                    {{ $click->clicked_at->timezone('Asia/Colombo')->format('M j, Y g:i A') }} - {{ $click->city ?? $click->country ?? 'Unknown' }} ({{ $click->device_type }})
                                </option>
                            @endforeach
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Clicks by Country -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Countries</h3>
                        <div class="h-64">
                            <canvas id="countriesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Geographic Map -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Geographic Distribution</h3>
                        <div id="map" class="h-64 rounded-lg"></div>
                    </div>
                </div>
            </div>

            <!-- Geographic Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Regions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Regions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($clicksByRegion as $region)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $region->region }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $region->count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No region data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Cities</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($clicksByCity as $city)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $city->city }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $city->count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No city data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device & Browser Analytics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Devices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Device Types</h3>
                        <div class="h-64">
                            <canvas id="devicesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Browsers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Browsers</h3>
                        <div class="h-64">
                            <canvas id="browsersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OS & Referrers -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Operating Systems -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Operating Systems</h3>
                        <div class="h-64">
                            <canvas id="osChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Referrers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const clicksData = @json($clicksOverTime);
        const countriesData = @json($clicksByCountry);
        const clickLocations = @json($clickLocations);
        const devicesData = @json($clicksByDevice);
        const browsersData = @json($clicksByBrowser);
        const osData = @json($clicksByOS);
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

        // Initialize Leaflet Map
        if (clickLocations.length > 0) {
            const map = L.map('map').setView([clickLocations[0].latitude, clickLocations[0].longitude], 3);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add markers for each click location
            clickLocations.forEach(location => {
                const marker = L.marker([location.latitude, location.longitude]).addTo(map);
                const popupContent = `
                    <div>
                        <strong>${location.city || 'Unknown'}</strong><br>
                        ${location.country || 'Unknown'}
                    </div>
                `;
                marker.bindPopup(popupContent);
            });

            // Fit map to show all markers
            const group = new L.featureGroup(clickLocations.map(loc =>
                L.marker([loc.latitude, loc.longitude])
            ));
            map.fitBounds(group.getBounds().pad(0.1));
        }

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

        // Browsers Chart
        const browsersCtx = document.getElementById('browsersChart').getContext('2d');
        new Chart(browsersCtx, {
            type: 'pie',
            data: {
                labels: browsersData.map(item => item.browser),
                datasets: [{
                    data: browsersData.map(item => item.count),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // OS Chart
        const osCtx = document.getElementById('osChart').getContext('2d');
        new Chart(osCtx, {
            type: 'bar',
            data: {
                labels: osData.map(item => item.os),
                datasets: [{
                    label: 'Clicks',
                    data: osData.map(item => item.count),
                    backgroundColor: 'rgba(79, 70, 229, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        function updateAnalytics() {
            const days = document.getElementById('daysFilter').value;
            const clickId = document.getElementById('clickFilter').value;
            let url = `{{ route('analytics.tracking-link', $trackingLink) }}?days=${days}`;
            if (clickId) {
                url += `&click_id=${clickId}`;
            }
            window.location.href = url;
        }
    </script>
</x-app-layout>