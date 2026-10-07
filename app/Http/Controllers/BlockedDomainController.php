<?php

namespace App\Http\Controllers;

use App\Models\BlockedDomain;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BlockedDomainController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {
        $this->middleware('admin');
    }

    public function index()
    {
        $domains = BlockedDomain::latest()->paginate(20);

        return view('blocked-domains.index', compact('domains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        // Normalize domain
        $domain = strtolower(trim($validated['domain']));
        $domain = rtrim($domain, '.');

        BlockedDomain::create([
            'domain' => $domain,
            'reason' => $validated['reason'] ?? null,
        ]);

        $this->auditService->log('block_domain', 'BlockedDomain', null, [
            'domain' => $domain,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('blocked-domains.index')
            ->with('success', 'Domain blocked successfully!');
    }

    public function destroy(BlockedDomain $blockedDomain)
    {
        $blockedDomain->delete();

        $this->auditService->log('unblock_domain', 'BlockedDomain', $blockedDomain->id, [
            'domain' => $blockedDomain->domain,
        ]);

        return redirect()->route('blocked-domains.index')
            ->with('success', 'Domain unblocked successfully!');
    }
}
