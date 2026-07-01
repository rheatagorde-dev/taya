<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ── User Management ──────────────────────────────

    public function usersIndex()
    {
        $users = User::with('facility')->paginate(20);
        $facilities = Facility::all();

        return view('admin.users', compact('users', 'facilities'));
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,bjmp_staff,pao_lawyer,ngo_lawyer,court_admin,policy_advocate',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'facility_id' => $request->input('facility_id'),
        ]);

        AuditService::log('user_created', "User {$request->input('name')} created with role {$request->input('role')}");

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function usersUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,bjmp_staff,pao_lawyer,ngo_lawyer,court_admin,policy_advocate',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        $user->update($request->only(['name', 'role', 'facility_id']));

        AuditService::log('user_updated', "User {$user->name} updated to role {$request->input('role')}");

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function usersDestroy(User $user)
    {
        $name = $user->name;
        $user->delete();

        AuditService::log('user_deactivated', "User {$name} deactivated");

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function usersResetPassword(User $user)
    {
        $user->update(['password' => Hash::make('password')]);
        AuditService::log('user_password_reset', "Password reset to default for user {$user->name}");
        return redirect()->back()->with('success', 'Password reset to default successfully.');
    }

    public function usersChangePassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->update(['password' => Hash::make($request->input('new_password'))]);
        AuditService::log('user_password_changed', "Password changed manually for user {$user->name}");
        
        return redirect()->back()->with('success', "Password updated successfully for {$user->name}.");
    }

    public function usersBulkResetPasswords(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $count = 0;
        foreach ($request->input('user_ids') as $id) {
            $user = User::find($id);
            if ($user && $user->id !== auth()->id()) { // Don't allow bulk reset on self accidentally
                $user->update(['password' => Hash::make('password')]);
                $count++;
            }
        }

        if ($count > 0) {
            AuditService::log('bulk_password_reset', "Bulk reset passwords to default for {$count} users");
            return redirect()->back()->with('success', "Passwords reset to default for {$count} users.");
        }
        
        return redirect()->back()->with('error', 'No valid users selected for password reset.');
    }

    // ── Facility Management ──────────────────────────

    public function facilitiesIndex()
    {
        $facilities = Facility::withCount('detainees')->paginate(20);

        return view('admin.facilities', compact('facilities'));
    }

    public function facilitiesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'capacity' => 'required|integer|min:1',
        ]);

        Facility::create($request->only(['name', 'region', 'address', 'capacity']));

        AuditService::log('facility_created', "Facility {$request->input('name')} created");

        return redirect()->back()->with('success', 'Facility created successfully.');
    }

    public function facilitiesUpdate(Request $request, Facility $facility)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'capacity' => 'required|integer|min:1',
        ]);

        $facility->update($request->only(['name', 'region', 'address', 'capacity']));

        AuditService::log('facility_updated', "Facility {$facility->name} updated");

        return redirect()->back()->with('success', 'Facility updated successfully.');
    }

    public function facilitiesDestroy(Facility $facility)
    {
        $name = $facility->name;
        $facility->delete();

        AuditService::log('facility_deleted', "Facility {$name} deleted");

        return redirect()->back()->with('success', 'Facility deleted successfully.');
    }

    // ── Penalty Reference Management ─────────────────

    public function penaltiesIndex()
    {
        $penalties = PenaltyReference::paginate(20);

        return view('admin.penalties', compact('penalties'));
    }

    public function penaltiesStore(Request $request)
    {
        $request->validate([
            'rpc_code' => 'required|string|max:50',
            'charge_name' => 'required|string|max:255',
            'max_penalty_years' => 'required|numeric|min:0',
            'max_penalty_months' => 'nullable|integer|min:0',
            'law_source' => 'required|in:RPC,RA,PD,EO,OTHER',
        ]);

        PenaltyReference::create([
            ...$request->only(['rpc_code', 'charge_name', 'max_penalty_years', 'max_penalty_months', 'law_source']),
            'last_validated' => now(),
        ]);

        AuditService::log('penalty_created', "Penalty reference {$request->input('charge_name')} created");

        return redirect()->back()->with('success', 'Penalty reference created successfully.');
    }

    public function penaltiesUpdate(Request $request, PenaltyReference $penalty)
    {
        $request->validate([
            'rpc_code' => 'required|string|max:50',
            'charge_name' => 'required|string|max:255',
            'max_penalty_years' => 'required|numeric|min:0',
            'max_penalty_months' => 'nullable|integer|min:0',
            'law_source' => 'required|in:RPC,RA,PD,EO,OTHER',
        ]);

        $penalty->update([
            ...$request->only(['rpc_code', 'charge_name', 'max_penalty_years', 'max_penalty_months', 'law_source']),
            'last_validated' => now(),
        ]);

        AuditService::log('penalty_updated', "Penalty reference {$penalty->charge_name} updated");

        return redirect()->back()->with('success', 'Penalty reference updated successfully.');
    }

    public function penaltiesDestroy(PenaltyReference $penalty)
    {
        $name = $penalty->charge_name;
        $penalty->delete();

        AuditService::log('penalty_deleted', "Penalty reference {$name} deleted");

        return redirect()->back()->with('success', 'Penalty reference deleted successfully.');
    }
}
