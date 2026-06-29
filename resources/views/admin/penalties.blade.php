@extends('layouts.app')

@section('header', 'Penalty References')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Penalty Reference Matrix</h2>
            <p class="text-sm text-gray-500 mt-1">Manage RPC/RA charges and their maximum imposable penalties.</p>
        </div>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">RPC/RA Code</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Charge Description</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Max Penalty (Years)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Max Penalty (Months)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penalties as $penalty)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $penalty->rpc_code }}
                                <span class="badge bg-gray-100 text-gray-600 ml-2">{{ $penalty->law_source }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $penalty->charge_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900">
                                {{ $penalty->max_penalty_years }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                {{ $penalty->max_penalty_months ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <form action="{{ route('admin.penalties.destroy', $penalty) }}" method="POST" onsubmit="return confirm('Warning: Deleting this reference might affect existing detainee computations. Proceed?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No penalty references found. Please run the seeder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($penalties->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $penalties->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
