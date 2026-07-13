<?php

namespace App\Http\Controllers;

use App\Models\Detainee;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function lookup(Request $request)
    {
        $code = $request->input('code');
        $error = null;

        if ($code) {
            $detainee = Detainee::where('tracking_code', strtoupper($code))
                ->where('tracking_enabled', true)
                ->first();

            if (!$detainee) {
                $error = 'Tracking code not found or is not active. Please verify the code and try again.';
            } else {
                return redirect()->route('tracking.show', $detainee->tracking_code);
            }
        }

        return view('tracking.lookup', compact('error'));
    }

    public function show(Request $request, string $code)
    {
        $detainee = Detainee::where('tracking_code', strtoupper($code))
            ->where('tracking_enabled', true)
            ->firstOrFail();

        $detainee->load([
            'facility',
            'penaltyReference',
            'phases' => fn($q) => $q->orderBy('phase_number'),
            'alerts' => fn($q) => $q->latest()->limit(1)->with('assignedUser'),
        ]);

        $latestAlert = $detainee->alerts->first();

        return view('tracking.show', compact('detainee', 'latestAlert'));
    }
}
