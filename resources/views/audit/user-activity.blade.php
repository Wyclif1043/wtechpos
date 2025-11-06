@extends('layouts.app')

@section('breadcrumbs')
<li>
    <span class="text-gray-500">/</span>
    <a href="{{ route('audit.index') }}" class="text-gray-500 hover:text-gray-700">Audit Log</a>
</li>
<li>
    <span class="text-gray-500">/</span>
    <span class="text-gray-900">{{ $user->name }}'s Activities</span>
</li>
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}'s Activities</h1>
                <p class="text-gray-600">{{ $user->email }} • {{ ucfirst($user->role) }}</p>
            </div>
            <a href="{{ route('audit.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>Back to Audit Log
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $activities->total() }}</div>
                <div class="text-sm text-gray-600">Total Activities</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-green-600">{{ $activities->where('event', 'created')->count() }}</div>
                <div class="text-sm text-gray-600">Created</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $activities->where('event', 'updated')->count() }}</div>
                <div class="text-sm text-gray-600">Updated</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-red-600">{{ $activities->where('event', 'deleted')->count() }}</div>
                <div class="text-sm text-gray-600">Deleted</div>
            </div>
        </div>

        <!-- Activities Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($activities as $activity)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $activity->created_at->format('M j, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $activity->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($activity->subject)
                                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($activity->event == 'created') bg-green-100 text-green-800
                                @elseif($activity->event == 'updated') bg-blue-100 text-blue-800
                                @elseif($activity->event == 'deleted') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($activity->event) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('audit.show', $activity) }}" class="text-blue-600 hover:text-blue-900">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection