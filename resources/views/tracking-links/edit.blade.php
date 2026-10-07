<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Tracking Link
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('tracking-links.update', $trackingLink) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Name
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name', $trackingLink->name) }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destination URL -->
                            <div>
                                <label for="destination_url" class="block text-sm font-medium text-gray-700">
                                    Destination URL
                                </label>
                                <input
                                    type="url"
                                    name="destination_url"
                                    id="destination_url"
                                    value="{{ old('destination_url', $trackingLink->destination_url) }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('destination_url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Campaign -->
                            <div>
                                <label for="campaign_id" class="block text-sm font-medium text-gray-700">
                                    Campaign
                                </label>
                                <select
                                    id="campaign_id"
                                    name="campaign_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">No Campaign</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}" {{ old('campaign_id', $trackingLink->campaign_id) == $campaign->id ? 'selected' : '' }}>
                                            {{ $campaign->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('campaign_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Custom Domain -->
                            <div>
                                <label for="link_domain_id" class="block text-sm font-medium text-gray-700">
                                    Custom Domain
                                </label>
                                <select
                                    id="link_domain_id"
                                    name="link_domain_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">Use default tracking domain</option>
                                    @foreach($linkDomains as $domain)
                                        <option value="{{ $domain->id }}" {{ old('link_domain_id', $trackingLink->link_domain_id) == $domain->id ? 'selected' : '' }}>
                                            {{ $domain->domain }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('link_domain_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">
                                    Status
                                </label>
                                <select
                                    id="status"
                                    name="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="active" {{ old('status', $trackingLink->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="disabled" {{ old('status', $trackingLink->status) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Expiration -->
                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700">
                                    Expiration Date (optional)
                                </label>
                                <input
                                    type="datetime-local"
                                    name="expires_at"
                                    id="expires_at"
                                    value="{{ old('expires_at', $trackingLink->expires_at ? $trackingLink->expires_at->format('Y-m-d\TH:i') : '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('expires_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Tracking Link
                            </button>
                            <a href="{{ route('tracking-links.show', $trackingLink) }}" class="text-gray-600 hover:text-gray-900">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
