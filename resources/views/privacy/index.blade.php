<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Privacy Settings
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Data Retention & Privacy</h3>

                    <form method="POST" action="{{ route('privacy.update') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Data Retention Days -->
                            <div>
                                <label for="data_retention_days" class="block text-sm font-medium text-gray-700">
                                    Data Retention Period
                                </label>
                                <select
                                    id="data_retention_days"
                                    name="data_retention_days"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="7" {{ $settings->data_retention_days == 7 ? 'selected' : '' }}>7 days</option>
                                    <option value="30" {{ $settings->data_retention_days == 30 ? 'selected' : '' }}>30 days</option>
                                    <option value="60" {{ $settings->data_retention_days == 60 ? 'selected' : '' }}>60 days</option>
                                    <option value="90" {{ $settings->data_retention_days == 90 ? 'selected' : '' }}>90 days</option>
                                    <option value="180" {{ $settings->data_retention_days == 180 ? 'selected' : '' }}>180 days</option>
                                    <option value="365" {{ $settings->data_retention_days == 365 ? 'selected' : '' }}>365 days</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    Click data older than this period will be automatically deleted.
                                </p>
                            </div>

                            <!-- Anonymize IPs -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="anonymize_ips"
                                        name="anonymize_ips"
                                        type="checkbox"
                                        {{ $settings->anonymize_ips ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="anonymize_ips" class="font-medium text-gray-700">
                                        Anonymize IP Addresses
                                    </label>
                                    <p class="text-gray-500">
                                        Store only the first three octets of IP addresses (e.g., 192.168.1.xxx). This provides location context while protecting individual privacy.
                                    </p>
                                </div>
                            </div>

                            <!-- Store Raw User Agents -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="store_raw_user_agents"
                                        name="store_raw_user_agents"
                                        type="checkbox"
                                        {{ $settings->store_raw_user_agents ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="store_raw_user_agents" class="font-medium text-gray-700">
                                        Store Raw User Agents
                                    </label>
                                    <p class="text-gray-500">
                                        Store complete user agent strings. When disabled, only parsed device/browser information is retained.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Privacy Information</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Location data is approximate and derived from IP addresses</li>
                        <li>• Data retention cleanup runs daily</li>
                        <li>• Deleted tracking links preserve analytics data</li>
                        <li>• Bots are excluded from human analytics</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
