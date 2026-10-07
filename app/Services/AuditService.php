<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event.
     */
    public function log(string $action, string $resourceType, ?int $resourceId = null, ?array $metadata = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => Request::ip(),
            'metadata' => $metadata,
        ]);
    }
}
