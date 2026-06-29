@extends('layouts.app')

@section('header', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-900">System Users</h2>
        <button x-data @click="$dispatch('open-modal', 'create-user')" class="btn-primary flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New User
        </button>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Name & Email</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Role</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility Assignment</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Joined</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ $user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $roleColor = match($user->role) {
                                        'admin' => 'purple',
                                        'bjmp_staff' => 'blue',
                                        'pao_lawyer', 'ngo_lawyer' => 'indigo',
                                        'court_admin' => 'teal',
                                        'policy_advocate' => 'orange',
                                        default => 'gray'
                                    };
                                @endphp
                                <span class="badge bg-{{ $roleColor }}-100 text-{{ $roleColor }}-800">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $user->facility ? $user->facility->name : 'System Wide' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Remove this user from the system?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">
                                            Revoke Access
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create User Modal (AlpineJS driven) -->
<div x-data="{ show: false }"
     x-show="show"
     x-on:open-modal.window="if ($event.detail === 'create-user') show = true"
     x-on:keydown.escape.window="show = false"
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" 
             @click="show = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-5">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Provision New User</h3>
                        <p class="text-sm text-gray-500 mt-1">Create a new system account. Default password will be 'password'.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-taya-accent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-taya-accent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-taya-accent">
                                <option value="bjmp_staff">BJMP Staff</option>
                                <option value="pao_lawyer">PAO Lawyer</option>
                                <option value="ngo_lawyer">NGO Lawyer</option>
                                <option value="court_admin">Court Administrator</option>
                                <option value="policy_advocate">Policy Advocate</option>
                                <option value="admin">System Admin</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Facility Assignment (Optional for non-BJMP)</label>
                            <select name="facility_id" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-taya-accent">
                                <option value="">System Wide / None</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-taya-accent text-base font-medium text-white hover:bg-taya-accent-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Create Account
                    </button>
                    <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
