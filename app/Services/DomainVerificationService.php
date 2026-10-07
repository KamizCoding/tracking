<?php

namespace App\Services;

use App\Models\LinkDomain;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DomainVerificationService
{
    /**
     * Verify a domain using DNS TXT record.
     */
    public function verifyDomain(LinkDomain $linkDomain): bool
    {
        $expectedRecord = $linkDomain->getExpectedDnsRecord();

        try {
            $records = dns_get_record($linkDomain->domain, DNS_TXT);

            if (! $records) {
                $this->logVerificationAttempt($linkDomain, false, 'No TXT records found');

                return false;
            }

            foreach ($records as $record) {
                if (isset($record['txt']) && trim($record['txt']) === $expectedRecord) {
                    $linkDomain->update([
                        'verified' => true,
                        'verified_at' => now(),
                        'status' => 'verified',
                    ]);

                    $this->logVerificationAttempt($linkDomain, true, 'DNS verification successful');

                    return true;
                }
            }

            $this->logVerificationAttempt($linkDomain, false, 'TXT record does not match expected value');

            return false;
        } catch (\Exception $e) {
            Log::error('DNS verification error', [
                'domain' => $linkDomain->domain,
                'error' => $e->getMessage(),
            ]);

            $this->logVerificationAttempt($linkDomain, false, 'DNS lookup error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Validate domain format.
     */
    public function validateDomain(string $domain): array
    {
        $validator = Validator::make(['domain' => $domain], [
            'domain' => 'required|string|max:255|regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors());
        }

        // Normalize domain
        $domain = strtolower(trim($domain));
        $domain = rtrim($domain, '.');

        return ['domain' => $domain];
    }

    /**
     * Check if domain is owned by the user.
     */
    public function isDomainOwnedByUser(string $domain, int $userId): bool
    {
        return LinkDomain::where('domain', $domain)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get the domain status lifecycle.
     */
    public function getDomainStatus(LinkDomain $linkDomain): string
    {
        if ($linkDomain->verified && $linkDomain->status === 'verified') {
            return 'verified';
        }

        if ($linkDomain->status === 'disabled') {
            return 'disabled';
        }

        if ($linkDomain->last_verification_attempt && $linkDomain->last_verification_attempt->diffInHours() < 24) {
            return 'pending';
        }

        return 'failed';
    }

    /**
     * Update domain status based on verification state.
     */
    public function updateDomainStatus(LinkDomain $linkDomain): void
    {
        if ($linkDomain->verified) {
            $linkDomain->status = 'verified';
        } elseif ($linkDomain->last_verification_attempt && $linkDomain->last_verification_attempt->diffInHours() >= 24) {
            $linkDomain->status = 'failed';
        } else {
            $linkDomain->status = 'pending';
        }

        $linkDomain->save();
    }

    /**
     * Log verification attempt.
     */
    protected function logVerificationAttempt(LinkDomain $linkDomain, bool $success, string $message): void
    {
        $linkDomain->update([
            'last_verification_attempt' => now(),
        ]);

        Log::info('Domain verification attempt', [
            'domain' => $linkDomain->domain,
            'success' => $success,
            'message' => $message,
        ]);
    }
}
