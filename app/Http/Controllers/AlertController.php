<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAlertRequest;
use App\Models\Alert;
use App\Models\Facility;
use App\Models\User;
use App\Notifications\AlertNotification;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::with(['detainee.facility', 'detainee.penaltyReference', 'detainee.phases', 'assignedUser', 'computation']);

        if ($level = $request->input('alert_level')) {
            $query->where('alert_level', $level);
        }

        if ($facility = $request->input('facility_id')) {
            $query->whereHas('detainee', fn($q) => $q->where('facility_id', $facility));
        }

        if ($from = $request->input('date_from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        if (!$request->has('show_resolved')) {
            $query->whereNull('resolved_at');
        }

        $alerts = $query->orderByRaw("CASE alert_level
            WHEN 'critical' THEN 1
            WHEN 'at_risk' THEN 2
            WHEN 'flagged' THEN 3
            WHEN 'monitored' THEN 4
            ELSE 5 END")
            ->paginate(20)
            ->withQueryString();

        $facilities = Facility::all();

        return view('alerts.index', compact('alerts', 'facilities'));
    }

    public function show(Alert $alert)
    {
        $alert->load([
            'detainee.facility',
            'detainee.penaltyReference',
            'detainee.phases',
            'computation',
            'assignedUser',
            'legalActions.filedByUser',
        ]);

        $lawyers = User::whereIn('role', ['pao_lawyer', 'ngo_lawyer'])->get();

        return view('alerts.show', compact('alert', 'lawyers'));
    }

    public function assign(Request $request, Alert $alert)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $alert->update(['assigned_to' => $request->input('assigned_to')]);

        $assignedUser = User::find($request->input('assigned_to'));

        // Notify the assigned lawyer
        if (in_array($alert->alert_level, ['critical', 'at_risk'])) {
            $assignedUser->notify(new AlertNotification($alert));
        }

        AuditService::log(
            'alert_assigned',
            "Alert #{$alert->id} assigned to {$assignedUser->name}",
            $alert->detainee_id
        );

        return redirect()->back()->with('success', "Alert assigned to {$assignedUser->name}.");
    }

    public function resolve(Request $request, Alert $alert)
    {
        $alert->update([
            'resolved_at' => now(),
            'alert_level' => 'resolved',
        ]);

        AuditService::log(
            'alert_resolved',
            "Alert #{$alert->id} resolved by {$request->user()->name}",
            $alert->detainee_id
        );

        return redirect()->back()->with('success', 'Alert resolved successfully.');
    }

    public function adminOverride(UpdateAlertRequest $request, Alert $alert)
    {
        $alert->update([
            'alert_level' => $request->input('alert_level'),
            'admin_override' => true,
            'override_note' => $request->input('override_note'),
        ]);

        AuditService::log(
            'alert_override',
            "Alert #{$alert->id} overridden to {$request->input('alert_level')} by admin: {$request->input('override_note')}",
            $alert->detainee_id
        );

        return redirect()->back()->with('success', 'Alert level overridden successfully.');
    }
}
