<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetaineeRequest;
use App\Models\Detainee;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Services\AuditService;
use App\Services\PhaseComplianceService;
use Illuminate\Http\Request;

class DetaineeController extends Controller
{
    public function __construct(
        protected PhaseComplianceService $phaseService
    ) {}

    public function index(Request $request)
    {
        $query = Detainee::with(['facility', 'penaltyReference', 'alerts' => fn($q) => $q->latest()->limit(1)]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('charge_description', 'like', "%{$search}%");
            });
        }

        if ($facility = $request->input('facility_id')) {
            $query->where('facility_id', $facility);
        }

        if ($recordFilter = $request->input('record_filter')) {
            [$type, $value] = explode(':', $recordFilter, 2) + [null, null];

            if ($type === 'status' && $value) {
                $query->where('status', $value);
            } elseif ($type === 'alert' && $value) {
                $query->whereHas('alerts', fn($q) => $q->where('alert_level', $value));
            }
        }

        $detainees = $query->latest()->paginate(20)->withQueryString();
        $facilities = Facility::all();

        return view('detainees.index', compact('detainees', 'facilities'));
    }

    public function create()
    {
        $facilities = Facility::all();
        $penalties = PenaltyReference::orderBy('charge_name')->get();

        return view('detainees.create', compact('facilities', 'penalties'));
    }

    public function store(StoreDetaineeRequest $request)
    {
        $detainee = Detainee::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'status' => 'active',
        ]);

        // Initialize 4 compliance phases
        $this->phaseService->initializePhases($detainee);

        // Compute overstay
        $this->phaseService->computeOverstay($detainee);

        AuditService::log('detainee_created', "Detainee {$detainee->full_name} created", $detainee->id);

        return redirect()->route('detainees.show', $detainee)
            ->with('success', 'Detainee record created successfully. Phases initialized and overstay computed.');
    }

    public function show(Detainee $detainee)
    {
        $detainee->load([
            'facility',
            'penaltyReference',
            'phases' => fn($q) => $q->orderBy('phase_number'),
            'overstayComputations' => fn($q) => $q->latest()->limit(1),
            'alerts' => fn($q) => $q->latest()->limit(1),
            'documents' => fn($q) => $q->with('uploadedByUser')->latest(),
            'legalActions' => fn($q) => $q->with(['filedByUser', 'alert'])->latest(),
            'auditLogs' => fn($q) => $q->with('user')->latest('created_at')->limit(20),
        ]);

        $latestComputation = $detainee->overstayComputations->first();
        $latestAlert = $detainee->alerts->first();

        return view('detainees.show', compact('detainee', 'latestComputation', 'latestAlert'));
    }

    public function edit(Detainee $detainee)
    {
        $facilities = Facility::all();
        $penalties = PenaltyReference::orderBy('charge_name')->get();

        return view('detainees.edit', compact('detainee', 'facilities', 'penalties'));
    }

    public function update(StoreDetaineeRequest $request, Detainee $detainee)
    {
        $detainee->update($request->validated());

        if ($detainee->wasChanged('commitment_date')) {
            $this->phaseService->reschedulePhases($detainee);
        }

        $this->phaseService->computeOverstay($detainee);

        AuditService::log('detainee_updated', "Detainee {$detainee->full_name} updated", $detainee->id);

        return redirect()->route('detainees.show', $detainee)
            ->with('success', 'Detainee record updated successfully.');
    }

    public function destroy(Detainee $detainee)
    {
        $name = $detainee->full_name;
        $detainee->update(['status' => 'archived']);

        AuditService::log('detainee_archived', "Detainee {$name} archived", $detainee->id);

        return redirect()->route('detainees.index')
            ->with('success', 'Detainee record archived successfully.');
    }

    public function release(Request $request, Detainee $detainee)
    {
        $name = $detainee->full_name;

        $totalPhases = $detainee->phases()->count();
        $completedPhases = $detainee->phases()->where('completed', true)->count();
        $allPhasesComplete = $totalPhases > 0 && $completedPhases === $totalPhases;
        $hasUnresolvedAlerts = $detainee->alerts()->whereNull('resolved_at')->exists();
        $requiresOverride = !$allPhasesComplete || $hasUnresolvedAlerts;

        if ($requiresOverride) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);
        }

        $detainee->update(['status' => 'released']);

        // Resolve any open alerts automatically when detainee is released
        $detainee->alerts()->whereNull('resolved_at')->update([
            'resolved_at' => now(),
            'alert_level' => 'resolved'
        ]);

        AuditService::log('detainee_released', "Detainee {$name} marked as released", $detainee->id);

        return redirect()->route('detainees.show', $detainee)
            ->with('success', 'Detainee record marked as released. Any open alerts have been automatically resolved.');
    }
}
