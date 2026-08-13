@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Part Sales Invoices</h4>
        <div>
            <a href="{{ route('admin.part-sales-invoices.outstanding') }}" class="btn btn-warning me-2"><i class="bx bx-list-check"></i> Outstanding</a>
            <a href="{{ route('admin.part-sales-invoices.export', ['search' => request('search')]) }}" class="btn btn-outline-success me-2"><i class="bx bx-file-export"></i> Export</a>
            <a href="{{ route('admin.part-sales-invoices.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> New Part Sales Invoice</a>
        </div>
    </div>

    <!-- Search filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.part-sales-invoices.index') }}">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by Invoice No, Customer Name or Mobile" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-search"></i> Search</button>
                            <a href="{{ route('admin.part-sales-invoices.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>GSTIN</th>
                        <th>Items Count</th>
                        <th>Taxable Amount</th>
                        <th>GST (CGST+SGST)</th>
                        <th>Grand Total</th>
                        <th>Payment Mode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Action
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('admin.part-sales-invoices.show', $inv) }}" class="dropdown-item">
                                            <i class="bx bx-show me-1 text-info"></i> View Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.part-sales-invoices.pdf', $inv) }}" class="dropdown-item">
                                            <i class="bx bxs-file-pdf me-1 text-danger"></i> Download PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item" onclick="directPrintPdf('{{ route('admin.part-sales-invoices.pdf', $inv) }}')">
                                            <i class="bx bx-printer me-1 text-dark"></i> Print PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.part-sales-invoices.edit', $inv) }}" class="dropdown-item">
                                            <i class="bx bx-edit me-1 text-primary"></i> Edit Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item quick-date-btn" data-id="{{ $inv->id }}" data-url="{{ route('admin.part-sales-invoices.quick-update-date', $inv) }}" data-number="{{ $inv->invoice_number }}" data-date="{{ $inv->invoice_date->format('Y-m-d') }}">
                                            <i class="bx bx-calendar-edit me-1 text-warning"></i> Quick Edit Date & No
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item text-danger delete-btn" data-id="{{ $inv->id }}" data-url="{{ route('admin.part-sales-invoices.destroy', $inv) }}">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td><a href="{{ route('admin.part-sales-invoices.show', $inv) }}" class="fw-bold">{{ $inv->invoice_number }}</a></td>
                        <td>{{ $inv->invoice_date->format('d-m-Y') }}</td>
                        <td>
                            {{ $inv->customer_name }}
                            @if($inv->customer_mobile) <br><small class="text-muted">{{ $inv->customer_mobile }}</small> @endif
                        </td>
                        <td>{{ $inv->customer_gstin ?? '-' }}</td>
                        <td>{{ $inv->items->count() }}</td>
                        <td>{{ number_format($inv->taxable_amount, 2) }}</td>
                        <td>{{ number_format($inv->cgst_amount + $inv->sgst_amount, 2) }}</td>
                        <td><strong>{{ number_format($inv->total_amount, 2) }}</strong></td>
                        <td>{{ $inv->payment_mode }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">No parts sales invoices recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $invoices->links() }}</div>
    </div>
</div>

<!-- Modal Quick Edit Invoice Date & Number -->
<div class="modal fade" id="quickDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-calendar-edit me-1"></i> Edit Invoice Date & Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickDateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="quickDateAlert" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" id="quick_invoice_number" name="invoice_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" id="quick_invoice_date" name="invoice_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveQuickDate"><i class="bx bx-save me-1"></i> Update Date & Number</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST">@csrf</form>
@endsection

@section('script')
<script>
$(function(){
    var quickModal = new bootstrap.Modal(document.getElementById('quickDateModal'));
    var quickForm = $('#quickDateForm');

    $(document).on('click', '.quick-date-btn', function(e){
        e.preventDefault();
        var btn = $(this);
        quickForm.attr('action', btn.data('url'));
        $('#quick_invoice_number').val(btn.data('number'));
        $('#quick_invoice_date').val(btn.data('date'));
        $('#quickDateAlert').addClass('d-none');
        quickModal.show();
    });

    quickForm.submit(function(e){
        e.preventDefault();
        var btn = $('#btnSaveQuickDate');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        $('#quickDateAlert').addClass('d-none');

        $.post(quickForm.attr('action'), quickForm.serialize())
        .done(function(res){
            if (res.success) {
                location.reload();
            } else {
                $('#quickDateAlert').text(res.message || 'Error updating date').removeClass('d-none');
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Update Date & Number');
            }
        })
        .fail(function(xhr){
            btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Update Date & Number');
            var msg = 'Validation error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $('#quickDateAlert').html(msg).removeClass('d-none');
        });
    });

    $(document).on('click', '.delete-btn', function(e){
        e.preventDefault();
        var form=$('#deleteForm'), url=$(this).data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the parts sales invoice and restore the parts stock in inventory.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.attr('action', url);
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
