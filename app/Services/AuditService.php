<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an action to the audit trail.
     */
    public static function log(string $action, string $description, ?int $detaineeId = null, ?int $userId = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'detainee_id' => $detaineeId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
