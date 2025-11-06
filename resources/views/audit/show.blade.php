@extends('layouts.app')

@section('breadcrumbs')
<li>
    <span class="text-gray-500">/</span>
    <a href="{{ route('audit.index') }}" class="text-gray-500 hover:text-gray-700">Audit Log</a>
</li>
<li>
    <span class="text-gray-500">/</span>
    <span class="text-gray-900">Activity Details</span>
</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Activity Details</h1>
            <a href="{{ route('audit.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>Back to Audit Log
            </a>
        </div>

        <!-- Activity Card -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <!-- Activity Header -->
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $activity->description }}
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Activity ID: {{ $activity->id }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        @if($activity->event == 'created') bg-green-100 text-green-800
                        @elseif($activity->event == 'updated') bg-blue-100 text-blue-800
                        @elseif($activity->event == 'deleted') bg-red-100 text-red-800
                        @elseif($activity->event == 'logged in') bg-green-100 text-green-800
                        @elseif($activity->event == 'logged out') bg-gray-100 text-gray-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($activity->event) }}
                    </span>
                </div>
            </div>

            <!-- Activity Details -->
            <div class="border-t border-gray-200">
                <dl>
                    <!-- Basic Information -->
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activity->created_at->format('M j, Y H:i:s') }}
                            <span class="text-gray-500 text-xs ml-2">
                                ({{ $activity->created_at->diffForHumans() }})
                            </span>
                        </dd>
                    </div>

                    <!-- User Information -->
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Performed By</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($activity->causer)
                                <div class="flex items-center">
                                    <span class="font-medium">{{ $activity->causer->name }}</span>
                                    <span class="mx-2 text-gray-400">•</span>
                                    <span class="text-gray-600">{{ $activity->causer->email }}</span>
                                    <span class="mx-2 text-gray-400">•</span>
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                        {{ ucfirst($activity->causer->role) }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-500">System</span>
                            @endif
                        </dd>
                    </div>

                    <!-- Subject Information -->
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Subject</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($activity->subject)
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                        {{ class_basename($activity->subject_type) }}
                                    </span>
                                    <span class="text-gray-400">#</span>
                                    <span class="font-medium">{{ $activity->subject_id }}</span>
                                    
                                    @if(method_exists($activity->subject, 'getDisplayName'))
                                        <span class="text-gray-600">- {{ $activity->subject->getDisplayName() }}</span>
                                    @elseif(isset($activity->subject->name))
                                        <span class="text-gray-600">- {{ $activity->subject->name }}</span>
                                    @elseif(isset($activity->subject->title))
                                        <span class="text-gray-600">- {{ $activity->subject->title }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-500">No subject</span>
                            @endif
                        </dd>
                    </div>

                    <!-- Properties -->
                    @if($activity->properties && count($activity->properties) > 0)
                        <!-- Old Values -->
                        @if(isset($activity->properties['old']) && count($activity->properties['old']) > 0)
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Old Values</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <pre class="text-sm text-red-800 whitespace-pre-wrap">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </dd>
                        </div>
                        @endif

                        <!-- Attributes -->
                        @if(isset($activity->properties['attributes']) && count($activity->properties['attributes']) > 0)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                @if($activity->event == 'updated') New Values @else Attributes @endif
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <pre class="text-sm text-green-800 whitespace-pre-wrap">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </dd>
                        </div>
                        @endif

                        <!-- Raw Properties -->
                        @if((!isset($activity->properties['old']) || count($activity->properties['old']) === 0) && 
                            (!isset($activity->properties['attributes']) || count($activity->properties['attributes']) === 0))
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Properties</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                    <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </dd>
                        </div>
                        @endif
                    @else
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Properties</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:mt-0 sm:col-span-2">
                            No additional properties available
                        </dd>
                    </div>
                    @endif

                    <!-- Additional Information -->
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Log Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activity->log_name ?: 'default' }}
                        </dd>
                    </div>

                    <!-- Batch UUID -->
                    @if($activity->batch_uuid)
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Batch UUID</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono text-sm">
                            {{ $activity->batch_uuid }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between items-center">
            <div class="flex space-x-3">
                @if($activity->causer)
                <a href="{{ route('audit.user-activity', $activity->causer_id) }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-user mr-2"></i>View User Activities
                </a>
                @endif
            </div>
            
            <div class="flex space-x-3">
                <a href="{{ route('audit.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-list mr-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
pre {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.875rem;
    line-height: 1.25;
}
</style>
@endpush