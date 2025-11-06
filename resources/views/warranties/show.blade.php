@extends('layouts.app')

@section('title', 'Warranty Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Warranty Details - {{ $warranty->warranty_number }}</h3>
                    <div class="card-tools">
                        @can('edit_warranties')
                        <a href="{{ route('warranties.edit', $warranty) }}" class="btn btn-primary mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        <a href="{{ route('warranties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Warranty Number:</th>
                                    <td>{{ $warranty->warranty_number }}</td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $warranty->customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Product:</th>
                                    <td>{{ $warranty->product->name }}</td>
                                </tr>
                                <tr>
                                    <th>Sale Reference:</th>
                                    <td>{{ $warranty->sale->sale_number }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td><span class="badge badge-info">{{ ucfirst($warranty->type) }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Duration & Status</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Start Date:</th>
                                    <td>{{ $warranty->start_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>{{ $warranty->end_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $warranty->duration_months }} months</td>
                                </tr>
                                <tr>
                                    <th>Remaining Days:</th>
                                    <td>
                                        @if($warranty->is_active)
                                            <span class="badge badge-success">{{ $warranty->remaining_days }} days</span>
                                        @else
                                            <span class="badge badge-danger">Expired</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($warranty->status == 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($warranty->status == 'expired')
                                            <span class="badge badge-danger">Expired</span>
                                        @else
                                            <span class="badge badge-secondary">Void</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($warranty->terms)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Terms & Conditions</h5>
                            <div class="border p-3">
                                {!! nl2br(e($warranty->terms)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($warranty->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Notes</h5>
                            <div class="border p-3">
                                {!! nl2br(e($warranty->notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Claims Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Warranty Claims</h5>
                    @if($warranty->is_active)
                    <div class="card-tools">
                        <a href="{{ route('warranty-claims.create') }}?warranty_id={{ $warranty->id }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Claim
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($warranty->claims->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Claim #</th>
                                    <th>Date</th>
                                    <th>Issue Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warranty->claims as $claim)
                                <tr>
                                    <td>{{ $claim->claim_number }}</td>
                                    <td>{{ $claim->claim_date->format('M d, Y') }}</td>
                                    <td>{{ ucfirst($claim->issue_type) }}</td>
                                    <td>
                                        @if($claim->status == 'submitted')
                                            <span class="badge badge-info">Submitted</span>
                                        @elseif($claim->status == 'in_progress')
                                            <span class="badge badge-warning">In Progress</span>
                                        @elseif($claim->status == 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($claim->status == 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('warranty-claims.show', $claim) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No claims filed for this warranty.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Progress Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Warranty Progress</h5>
                </div>
                <div class="card-body text-center">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar {{ $warranty->coverage_progress > 80 ? 'bg-danger' : ($warranty->coverage_progress > 60 ? 'bg-warning' : 'bg-success') }}" 
                             role="progressbar" 
                             style="width: {{ $warranty->coverage_progress }}%"
                             aria-valuenow="{{ $warranty->coverage_progress }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ number_format($warranty->coverage_progress, 1) }}%
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            {{ $warranty->start_date->format('M d, Y') }} - {{ $warranty->end_date->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($warranty->is_active)
                        <a href="{{ route('warranty-claims.create') }}?warranty_id={{ $warranty->id }}" class="btn btn-warning">
                            <i class="fas fa-tools"></i> File Claim
                        </a>
                        @endif
                        @can('edit_warranties')
                        <a href="{{ route('warranties.edit', $warranty) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit Warranty
                        </a>
                        @endcan
                        <a href="{{ route('sales.show', $warranty->sale_id) }}" class="btn btn-outline-info">
                            <i class="fas fa-receipt"></i> View Sale
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection