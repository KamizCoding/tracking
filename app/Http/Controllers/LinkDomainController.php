<?php

namespace App\Http\Controllers;

use App\Models\LinkDomain;
use App\Services\AuditService;
use App\Services\DomainVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkDomainController extends Controller
{
    public function __construct(
        private DomainVerificationService $domainVerificationService,
        private AuditService $auditService,
    ) {}

    public function index()
    {
        $domains = LinkDomain::where('user_id', Auth::id())
            ->withCount('trackingLinks')
            ->latest()
            ->paginate(20);

        return view('link-domains.index', compact('domains'));
    }

    public function create()
    {
        return view('link-domains.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        // Validate and normalize domain
        $validated = $this->domainVerificationService->validateDomain($validated['domain']);
        $domain = $validated['domain'];

        // Check if domain already exists
        if (LinkDomain::where('domain', $domain)->exists()) {
            return back()->withInput()->withErrors(['domain' => 'This domain is already registered.']);
        }

        $linkDomain = LinkDomain::create([
            'user_id' => Auth::id(),
            'domain' => $domain,
            'status' => 'pending',
            'verified' => false,
        ]);

        // Generate verification token
        $linkDomain->generateVerificationToken();

        return redirect()->route('link-domains.index')
            ->with('success', 'Domain added successfully. Please verify ownership by adding the TXT record.');
    }

    public function verify(LinkDomain $linkDomain)
    {
        $this->authorize('update', $linkDomain);

        if ($this->domainVerificationService->verifyDomain($linkDomain)) {
            $this->auditService->log('verify', 'LinkDomain', $linkDomain->id, [
                'domain' => $linkDomain->domain,
            ]);

            return back()->with('success', 'Domain verified successfully!');
        }

        return back()->with('error', 'Domain verification failed. Please check your DNS TXT record.');
    }

    public function destroy(LinkDomain $linkDomain)
    {
        $this->authorize('delete', $linkDomain);

        $linkDomain->delete();

        return redirect()->route('link-domains.index')
            ->with('success', 'Domain removed successfully!');
    }
}
