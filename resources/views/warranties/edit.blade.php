@extends('layouts.app')

@section('title', 'Edit Warranty')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Warranty - {{ $warranty->warranty_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('warranties.update', $warranty) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type">Warranty Type *</label>
                                    <select name="type" id="type" class="form-control" required>
                                        @foreach($warrantyTypes as $key => $type)
                                        <option value="{{ $key }}" {{ $warranty->type == $key ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration_months">Duration (Months) *</label>
                                    <input type="number" name="duration_months" id="duration_months" class="form-control" min="1" value="{{ $warranty->duration_months }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date *</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $warranty->start_date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active" {{ $warranty->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="expired" {{ $warranty->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="void" {{ $warranty->status == 'void' ? 'selected' : '' }}>Void</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="terms">Terms & Conditions</label>
                            <textarea name="terms" id="terms" class="form-control" rows="3">{{ $warranty->terms }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2">{{ $warranty->notes }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Warranty</button>
                        <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection