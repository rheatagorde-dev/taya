<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Detainee;
use App\Models\DetaineePhase;
use App\Models\AuditLog;
use App\Models\Facility;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'bjmp_staff' => $this->bjmpDashboard($user),
            'pao_lawyer', 'ngo_lawyer' => $this->lawyerDashboard($user),
            'court_admin' => $this->lawyerDashboard($user),
            'policy_advocate' => $this->policyDashboard(),
            default => redirect('/'),
        };
    }

    protected function adminDashboard()
    {
        $stats = [
            'total_facilities' => Facility::count(),
            'total_detainees' => Detainee::where('status', 'active')->count(),
            'critical_alerts' => Alert::where('alert_level', 'critical')->whereNull('resolved_at')->count(),
            'resolved_this_month' => Alert::whereNotNull('resolved_at')
                ->where('resolved_at', '>=', now()->startOfMonth())
                ->count(),
            'unable_to_pay_bail' => Detainee::where('status', 'active')->where('bail_status', 'unable_to_pay')->count(),
            'overcrowded_facilities' => Facility::whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('detainees')
                    ->whereColumn('detainees.facility_id', 'facilities.id')
                    ->where('detainees.status', 'active')
                    ->groupBy('facility_id')
                    ->havingRaw('COUNT(*) > facilities.capacity');
            })->count(),
        ];

        $recentAuditLogs = AuditLog::with(['user', 'detainee'])
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentAuditLogs'));
    }

    protected function bjmpDashboard($user)
    {
        $facilityId = $user->facility_id;

        $stats = [
            'total_detainees' => Detainee::when($facilityId, fn($q) => $q->where('facility_id', $facilityId))
                ->where('status', 'active')->count(),
            'critical_alerts' => Alert::whereHas('detainee', fn($q) => $q->when($facilityId, fn($q2) => $q2->where('facility_id', $facilityId)))
                ->where('alert_level', 'critical')->whereNull('resolved_at')->count(),
            'at_risk' => Alert::whereHas('detainee', fn($q) => $q->when($facilityId, fn($q2) => $q2->where('facility_id', $facilityId)))
                ->where('alert_level', 'at_risk')->whereNull('resolved_at')->count(),
            'phases_overdue_today' => DetaineePhase::whereHas('detainee', fn($q) => $q->where('status', 'active')
                ->when($facilityId, fn($q2) => $q2->where('facility_id', $facilityId)))
                ->where('completed', false)->where('due_date', '<', now())->count(),
        ];

        $overduePhases = DetaineePhase::with(['detainee.facility'])
            ->whereHas('detainee', fn($q) => $q->where('status', 'active')
                ->when($facilityId, fn($q2) => $q2->where('facility_id', $facilityId)))
            ->where('completed', false)
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->take(20)
            ->get();

        return view('dashboard.bjmp', compact('stats', 'overduePhases'));
    }

    protected function lawyerDashboard($user)
    {
        $alerts = Alert::with(['detainee.facility', 'detainee.penaltyReference', 'assignedUser'])
            ->whereNull('resolved_at')
            ->orderByRaw("CASE alert_level
                WHEN 'critical' THEN 1
                WHEN 'at_risk' THEN 2
                WHEN 'flagged' THEN 3
                WHEN 'monitored' THEN 4
                ELSE 5 END")
            ->paginate(20);

        $facilities = Facility::all();

        return view('dashboard.lawyer', compact('alerts', 'facilities'));
    }

    protected function policyDashboard()
    {
        // Aggregate data for charts
        $alertsByLevel = Alert::whereNull('resolved_at')
            ->selectRaw('alert_level, COUNT(*) as count')
            ->groupBy('alert_level')
            ->pluck('count', 'alert_level')
            ->toArray();

        $detaineesByFacility = Detainee::where('status', 'active')
            ->join('facilities', 'detainees.facility_id', '=', 'facilities.id')
            ->selectRaw('facilities.name, COUNT(*) as count')
            ->groupBy('facilities.name')
            ->pluck('count', 'name')
            ->toArray();

        $resolutionsOverTime = Alert::whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subMonths(6))
            ->selectRaw("strftime('%Y-%m', resolved_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $unableToPayBail = Detainee::where('status', 'active')
            ->where('bail_status', 'unable_to_pay')
            ->count();

        $overcrowdedFacilities = Facility::whereExists(function ($query) {
            $query->selectRaw('1')
                ->from('detainees')
                ->whereColumn('detainees.facility_id', 'facilities.id')
                ->where('detainees.status', 'active')
                ->groupBy('facility_id')
                ->havingRaw('COUNT(*) > facilities.capacity');
        })->count();

        return view('dashboard.policy', compact('alertsByLevel', 'detaineesByFacility', 'resolutionsOverTime', 'unableToPayBail', 'overcrowdedFacilities'));
    }
}
