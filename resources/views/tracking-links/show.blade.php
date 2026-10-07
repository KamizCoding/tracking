<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tracking Link Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Link Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $trackingLink->name }}</h1>
                                <p class="text-sm text-gray-500 mt-1">Created: {{ $trackingLink->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $trackingLink->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $trackingLink->status }}
                            </span>
                        </div>

                        <!-- Tracking URL -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tracking URL (for users to click)</label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $trackingLink->tracking_url }}" readonly
                                    class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm">
                                <button onclick="copyToClipboard('{{ $trackingLink->tracking_url }}')" 
                                    class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-700 transition text-sm">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <!-- Destination URL with Code -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destination URL with Code (for reference)</label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ app(\App\Services\TrackingUrlService::class)->generateDestinationUrlWithCode($trackingLink) }}" readonly
                                    class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm">
                                <button onclick="copyToClipboard('{{ app(\App\Services\TrackingUrlService::class)->generateDestinationUrlWithCode($trackingLink) }}')" 
                                    class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-700 transition text-sm">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                            <div class="flex items-center gap-4">
                                <img src="{{ $trackingLink->qr_code }}" alt="QR Code" class="w-32 h-32 border border-gray-300 rounded-md">
                                <div class="flex flex-col gap-2">
                                    <button onclick="downloadQRCode('{{ $trackingLink->qr_code }}', '{{ $trackingLink->code }}')" 
                                        class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-700 transition text-sm">
                                        Download QR Code
                                    </button>
                                    <button onclick="copyToClipboard('{{ $trackingLink->tracking_url }}')" 
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition text-sm">
                                        Copy Link
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Destination URL -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destination URL</label>
                            <a href="{{ $trackingLink->destination_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 break-all">
                                {{ $trackingLink->destination_url }}
                            </a>
                        </div>

                        <!-- Additional Info -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Code:</span>
                                <span class="font-medium">{{ $trackingLink->code }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Campaign:</span>
                                <span class="font-medium">{{ $trackingLink->campaign?->name ?? 'None' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Total Clicks:</span>
                                <span class="font-medium">{{ $trackingLink->click_count }}</span>
                            </div>
                            @if($trackingLink->expires_at)
                                <div>
                                    <span class="text-gray-500">Expires:</span>
                                    <span class="font-medium">{{ $trackingLink->expires_at->format('M d, Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-gray-900">{{ $trackingLink->click_count }}</div>
                        <div class="text-sm text-gray-600 mt-1">Total Clicks</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-gray-900">{{ $trackingLink->clicks()->where('is_bot', false)->distinct('visitor_id')->count() }}</div>
                        <div class="text-sm text-gray-600 mt-1">Estimated Unique Visitors</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-gray-900">{{ $trackingLink->clicks()->where('is_bot', false)->whereNotNull('country')->distinct('country')->count() }}</div>
                        <div class="text-sm text-gray-600 mt-1">Countries</div>
                    </div>
                </div>

                <!-- Analytics Button -->
                <div class="mb-6">
                    <a href="{{ route('analytics.tracking-link', $trackingLink) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        View Detailed Analytics
                    </a>
                </div>

                <!-- Action Buttons -->
                <div class="mb-6 flex items-center gap-3">
                    <a href="{{ route('tracking-links.edit', $trackingLink) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Edit
                    </a>

                    @if($trackingLink->status === 'active')
                        <form method="POST" action="{{ route('tracking-links.disable', $trackingLink) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                                Disable
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('tracking-links.enable', $trackingLink) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Enable
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('tracking-links.destroy', $trackingLink) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this tracking link? Analytics data will be preserved.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>

                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('tracking-links.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                        ← Back to Links
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Link copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function downloadQRCode(dataUrl, code) {
            const link = document.createElement('a');
            link.href = dataUrl;
            link.download = 'qr-code-' + code + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</x-app-layout>