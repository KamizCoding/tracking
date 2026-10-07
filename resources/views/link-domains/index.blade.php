<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Custom Domains') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Custom Domains</h1>
                <a href="{{ route('link-domains.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    Add Custom Domain
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($domains->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking Links</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($domains as $domain)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $domain->domain }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $domain->tracking_links_count ?? 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($domain->status === 'verified')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                                            @elseif($domain->status === 'pending')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @elseif($domain->status === 'failed')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($domain->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($domain->status === 'pending' || $domain->status === 'failed')
                                                <form action="{{ route('link-domains.verify', $domain) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 mr-3">Verify</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('link-domains.destroy', $domain) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this domain?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if($domains->hasPages())
                            <div class="mt-4">
                                {{ $domains->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500">No custom domains configured yet.</p>
                            <a href="{{ route('link-domains.create') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900">
                                Add your first custom domain
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verification Instructions -->
            @if($domains->count() > 0 && $domains->contains('status', 'pending'))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">How to Verify Your Domain</h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                        <li>Add a TXT record to your domain's DNS configuration</li>
                        <li>Use the verification token shown below for each pending domain</li>
                        <li>Wait up to 24 hours for DNS propagation, then click "Verify"</li>
                    </ol>

                    <div class="mt-4 space-y-3">
                        @foreach($domains as $domain)
                            @if($domain->status === 'pending' && $domain->verification_token)
                                <div class="bg-white rounded-md p-3 border border-blue-200">
                                    <p class="font-medium text-blue-900">{{ $domain->domain }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $domain->verification_token }}</code>
                                        <button onclick="copyToClipboard('{{ $domain->verification_token }}')" class="text-xs text-blue-600 hover:text-blue-800">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Token copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</x-app-layout>