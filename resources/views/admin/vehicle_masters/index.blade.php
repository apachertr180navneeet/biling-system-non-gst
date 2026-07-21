@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Vehicle Master</h4>
        <div>
            <a href="{{ route('admin.vehicle-masters.import-template') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-download"></i> Template</a>
            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bx bx-upload"></i> Import</button>
            <a href="{{ route('admin.vehicle-masters.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
            <a href="{{ route('admin.vehicle-masters.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> New</a>
        </div>
    </div>
    
    @if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <strong>Import completed with some skipped rows/warnings:</strong>
        <ul class="mb-0 mt-2" style="max-height: 200px; overflow-y: auto;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vehicle-masters.index') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Variant, Color, Fuel Type or Transmission" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.vehicle-masters.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Variant</th>
                        <th>Color</th>
                        <th>Fuel</th>
                        <th>Transmission</th>
                        <th>Battery Type</th>
                        <th>Battery Make</th>
                        <th>Price</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $v)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $v->variant_name ?? '-' }}</td>
                        <td>{{ $v->color_name ?? '-' }}</td>
                        <td>{{ $v->fuel_type ?? '-' }}</td>
                        <td>{{ $v->transmission ?? '-' }}</td>
                        <td>{{ $v->battery_type ?? '-' }}</td>
                        <td>{{ $v->battery_make ?? '-' }}</td>
                        <td>{{ number_format($v->ex_showroom_price, 2) }}</td>
                        <td><span class="badge bg-label-secondary">{{ $v->min_stock ?? 0 }}</span></td>
                        <td>
                            <label class="switch switch-success">
                                <input type="checkbox" class="toggle-status"
                                    data-url="{{ route('admin.vehicle-masters.toggle-status', $v) }}"
                                    {{ $v->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <a href="{{ route('admin.vehicle-masters.edit', $v) }}" class="btn btn-sm btn-primary"><i class="bx bx-edit"></i></a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $v->id }}" data-url="{{ route('admin.vehicle-masters.destroy', $v) }}"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">No vehicle records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $vehicles->links() }}</div>
    </div>
</div>
<form id="deleteForm" method="POST">@csrf</form>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.vehicle-masters.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Vehicle Masters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt,.xls,.xlsx" required>
                        <div class="form-text text-muted mt-2">
                            Please upload a valid CSV file using the template headers:<br>
                            <code>variant_name, color_name, fuel_type, transmission, ex_showroom_price, battery_type, battery_make, min_stock</code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@section('script')
<script>
$(function(){
    $('.delete-btn').click(function(){
        var form=$('#deleteForm'),url=$(this).data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "Delete this vehicle?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.attr('action',url);
                $.post(url, form.serialize() + '&_method=DELETE').done(function(r){
                    if(r.success) location.reload();
                }).fail(function(){
                    Swal.fire('Error', 'Something went wrong!', 'error');
                });
            }
        });
    });
});
</script>
@endsection
@endsection
