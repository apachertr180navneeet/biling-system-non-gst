@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Spare Parts /</span> Edit
    </h4>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Edit Spare Part</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.spare-parts.update', $sparePart) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Part No</label>
                    <input type="text" name="part_no" class="form-control @error('part_no') is-invalid @enderror" value="{{ old('part_no', $sparePart->part_no) }}">
                    @error('part_no') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $sparePart->name) }}">
                    @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Selling Price</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', $sparePart->selling_price) }}">
                    @error('selling_price') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">MRP</label>
                    <input type="number" step="0.01" name="mrp" class="form-control @error('mrp') is-invalid @enderror" value="{{ old('mrp', $sparePart->mrp) }}">
                    @error('mrp') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $sparePart->unit) }}">
                    @error('unit') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Minimum Stock Level</label>
                    <input type="number" name="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', $sparePart->min_stock ?? 0) }}" min="0">
                    <div class="form-text text-muted">Low stock alert triggers when quantity falls to or below this level.</div>
                    @error('min_stock') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.spare-parts.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
@endsection
