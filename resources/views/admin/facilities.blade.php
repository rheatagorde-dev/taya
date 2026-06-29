@extends('layouts.app')

@section('header', 'Facilities')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">BJMP Facilities</h2>
        </div>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Region</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Address</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Capacity / Detainees</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($facilities as $facility)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $facility->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $facility->region }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $facility->address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="font-medium text-gray-900">{{ $facility->detainees_count }}</span>
                                <span class="text-gray-400">/ {{ $facility->capacity ?: 'N/A' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No facilities found. Please run the seeder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
