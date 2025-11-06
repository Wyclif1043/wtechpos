@extends('layouts.app')

@section('title', 'Warranty Claim Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Claim Details - {{ $warrantyClaim->claim_number }}</h3>
                    <div class="card-tools">
                        @can('edit_warranty_claims')
                        <a href="{{ route('warranty-claims.edit', $warrantyClaim) }}" class="btn btn-primary mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        <a href="{{ route('warranty-claims.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Claim Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Claim Number:</th>
                                    <td>{{ $warrantyClaim->claim_number }}</td>
                                </tr>
                                <tr>
                                    <th>Warranty:</th>
                                    <td>
                                        <a href="{{ route('warranties.show', $warrantyClaim->warranty_id) }}">
                                            {{ $warrantyClaim->warranty->warranty_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $warrantyClaim->customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Product:</th>
                                    <td>{{ $warrantyClaim->product->name }}</td>
                                </tr>
                                <tr>
                                    <th>Claim Date:</th>
                                    <td>{{ $warrantyClaim->claim_date->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Status & Resolution</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Issue Type:</th>
                                    <td>{{ ucfirst($warrantyClaim->issue_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($warrantyClaim->status == 'submitted')
                                            <span class="badge badge-info">Submitted</span>
                                        @elseif($warrantyClaim->status == 'in_progress')
                                            <span class="badge badge-warning">In Progress</span>
                                        @elseif($warrantyClaim->status == 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($warrantyClaim->status == 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($warrantyClaim->resolution_date)
                                <tr>
                                    <th>Resolution Date:</th>
                                    <td>{{ $warrantyClaim->resolution_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Resolved By:</th>
                                    <td>{{ $warrantyClaim->resolvedBy->name ?? 'N/A' }}</td>
                                </tr>
                                @endif
                                @if($warrantyClaim->repair_cost)
                                <tr>
                                    <th>Repair Cost:</th>
                                    <td>${{ number_format($warrantyClaim->repair_cost, 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Problem Description</h5>
                            <div class="border p-3 bg-light">
                                {!! nl2br(e($warrantyClaim->problem_description)) !!}
                            </div>
                        </div>
                    </div>

                    @if($warrantyClaim->resolution_notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Resolution Notes</h5>
                            <div class="border p-3 bg-light">
                                {!! nl2br(e($warrantyClaim->resolution_notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($warrantyClaim->customer_feedback)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Customer Feedback</h5>
                            <div class="border p-3 bg-light">
                                {!! nl2br(e($warrantyClaim->customer_feedback)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Status Update -->
            @can('edit_warranty_claims')
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('warranty-claims.update-status', $warrantyClaim) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="submitted" {{ $warrantyClaim->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="in_progress" {{ $warrantyClaim->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="approved" {{ $warrantyClaim->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $warrantyClaim->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="completed" {{ $warrantyClaim->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="resolution_notes">Resolution Notes</label>
                            <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="3" placeholder="Add resolution notes...">{{ $warrantyClaim->resolution_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                    </form>
                </div>
            </div>
            @endcan

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('warranties.show', $warrantyClaim->warranty_id) }}" class="btn btn-outline-info">
                            <i class="fas fa-file-contract"></i> View Warranty
                        </a>
                        <a href="{{ route('customers.show', $warrantyClaim->customer_id) }}" class="btn btn-outline-info">
                            <i class="fas fa-user"></i> View Customer
                        </a>
                        <a href="{{ route('products.show', $warrantyClaim->product_id) }}" class="btn btn-outline-info">
                            <i class="fas fa-box"></i> View Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection