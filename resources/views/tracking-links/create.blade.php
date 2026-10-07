<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Tracking Link') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Tracking Link</h1>

                        <form method="POST" action="{{ route('tracking-links.store') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="September Product Campaign">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="destination_url" class="block text-sm font-medium text-gray-700 mb-1">Destination URL</label>
                                <input type="url" id="destination_url" name="destination_url" value="{{ old('destination_url') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="https://example.com/product/123">
                                @error('destination_url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Custom Slug (Optional)</label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="products/wallet.html">
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Leave empty to auto-generate from destination URL path. Use custom slug for a cleaner tracking link.
                                </p>
                            </div>

                            <div class="mb-4">
                                <label for="campaign_id" class="block text-sm font-medium text-gray-700 mb-1">Campaign (Optional)</label>
                                <select id="campaign_id" name="campaign_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select a campaign</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                            {{ $campaign->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('campaign_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="link_domain_id" class="block text-sm font-medium text-gray-700 mb-1">Custom Domain (Optional)</label>
                                <select id="link_domain_id" name="link_domain_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Use default tracking domain</option>
                                    @foreach($linkDomains as $domain)
                                        <option value="{{ $domain->id }}" {{ old('link_domain_id') == $domain->id ? 'selected' : '' }}>
                                            {{ $domain->domain }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('link_domain_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Only verified custom domains are shown. Links will use the selected domain for tracking URLs.
                                </p>
                            </div>

                            <div class="mb-4">
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Expiration Date (Optional)</label>
                                <input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('expires_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('tracking-links.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-700 transition">
                                    Create Link
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>