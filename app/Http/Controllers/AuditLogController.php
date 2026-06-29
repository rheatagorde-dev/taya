<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'detainee']);

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($detaineeId = $request->input('detainee_id')) {
            $query->where('detainee_id', $detaineeId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($from = $request->input('date_from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        return view('admin.audit-logs', compact('logs'));
    }
}
