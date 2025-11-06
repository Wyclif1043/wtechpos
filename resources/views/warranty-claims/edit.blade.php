@extends('layouts.app')

@section('title', 'Edit Warranty Claim')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Warranty Claim - {{ $warrantyClaim->claim_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('warranty-claims.show', $warrantyClaim) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('warranty-claims.update', $warrantyClaim) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="issue_type">Issue Type *</label>
                                    <select name="issue_type" id="issue_type" class="form-control" required>
                                        @foreach($issueTypes as $key => $type)
                                        <option value="{{ $key }}" {{ $warrantyClaim->issue_type == $key ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select name="status" id="status" class="form-control" required>
                                        @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}" {{ $warrantyClaim->status == $key ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="problem_description">Problem Description *</label>
                            <textarea name="problem_description" id="problem_description" class="form-control" rows="4" required>{{ $warrantyClaim->problem_description }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="resolution_notes">Resolution Notes</label>
                            <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="3">{{ $warrantyClaim->resolution_notes }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="repair_cost">Repair Cost</label>
                                    <input type="number" name="repair_cost" id="repair_cost" class="form-control" step="0.01" min="0" value="{{ $warrantyClaim->repair_cost }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="resolution_date">Resolution Date</label>
                                    <input type="date" name="resolution_date" id="resolution_date" class="form-control" value="{{ $warrantyClaim->resolution_date ? $warrantyClaim->resolution_date->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="customer_feedback">Customer Feedback</label>
                            <textarea name="customer_feedback" id="customer_feedback" class="form-control" rows="2">{{ $warrantyClaim->customer_feedback }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Claim</button>
                        <a href="{{ route('warranty-claims.show', $warrantyClaim) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection