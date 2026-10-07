<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Custom Domain') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="mb-6">
                    <a href="{{ route('link-domains.index') }}" class="text-indigo-600 hover:text-indigo-900">
                        ← Back to Domains
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900 mt-2">Add Custom Domain</h1>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form method="POST" action="{{ route('link-domains.store') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Custom Domain</label>
                                <input type="text" id="domain" name="domain" required
                                    placeholder="go.example.com"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @error('domain')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-gray-500">
                                    Enter your custom domain without http:// or https://. You'll need to verify ownership via DNS TXT record.
                                </p>
                            </div>

                            <div class="flex items-center justify-end">
                                <a href="{{ route('link-domains.index') }}" class="mr-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                    Add Domain
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>