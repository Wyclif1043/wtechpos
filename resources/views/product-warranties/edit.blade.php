@extends('layouts.app')

@section('title', 'Edit Product Warranty')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Product Warranty - {{ $productWarranty->warranty_name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('product-warranties.show', $productWarranty) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('product-warranties.update', $productWarranty) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="product_id">Product</label>
                                    <input type="text" class="form-control bg-light" value="{{ $productWarranty->product->name }} (SKU: {{ $productWarranty->product->sku }})" readonly>
                                    <small class="form-text text-muted">Product cannot be changed after creation.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="warranty_name">Warranty Name *</label>
                                    <input type="text" name="warranty_name" id="warranty_name" class="form-control" value="{{ $productWarranty->warranty_name }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Warranty Type *</label>
                                    <select name="type" id="type" class="form-control" required>
                                        @foreach($warrantyTypes as $key => $type)
                                        <option value="{{ $key }}" {{ $productWarranty->type == $key ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_months">Duration (Months) *</label>
                                    <input type="number" name="duration_months" id="duration_months" class="form-control" min="1" value="{{ $productWarranty->duration_months }}" required>
                                    <small class="form-text text-muted" id="duration-display">
                                        {{ $productWarranty->formatted_duration }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="coverage_details">Coverage Details</label>
                            <textarea name="coverage_details" id="coverage_details" class="form-control" rows="3" placeholder="What does this warranty cover?">{{ $productWarranty->coverage_details }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="terms">Terms & Conditions</label>
                            <textarea name="terms" id="terms" class="form-control" rows="4" placeholder="Warranty terms and conditions...">{{ $productWarranty->terms }}</textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       class="form-check-input" {{ $productWarranty->is_active ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Active Warranty</label>
                            </div>
                            <small class="form-text text-muted">Inactive warranties won't be available for new sales.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Warranty</button>
                        <a href="{{ route('product-warranties.show', $productWarranty) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const durationInput = document.getElementById('duration_months');
    const durationDisplay = document.getElementById('duration-display');

    function updateDurationDisplay() {
        const months = parseInt(durationInput.value) || 0;
        if (months > 0) {
            if (months >= 12) {
                const years = Math.floor(months / 12);
                const remainingMonths = months % 12;
                let display = `${years} year${years > 1 ? 's' : ''}`;
                if (remainingMonths > 0) {
                    display += ` ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}`;
                }
                durationDisplay.textContent = display;
            } else {
                durationDisplay.textContent = `${months} month${months > 1 ? 's' : ''}`;
            }
        } else {
            durationDisplay.textContent = '';
        }
    }

    durationInput.addEventListener('input', updateDurationDisplay);
});
</script>
@endpush