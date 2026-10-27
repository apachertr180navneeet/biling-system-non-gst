@php
    $payments = \App\Models\PaymentTransaction::where('bill_type', $billType)
        ->where('bill_id', $billId)
        ->orderBy('created_at', 'desc')
        ->get();

    $rollbackRouteMap = [
        'vehicle_sales' => 'admin.vehicle-sales-invoices.rollback-payment',
        'part_sales' => 'admin.part-sales-invoices.rollback-payment',
        'vehicle_purchase' => 'admin.vehicle-purchase-orders.rollback-payment',
        'part_purchase' => 'admin.purchase-orders.rollback-payment',
    ];
    $rollbackRoute = $rollbackRouteMap[$billType] ?? 'admin.part-sales-invoices.rollback-payment';
@endphp

<div class="card shadow-sm mt-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bx bx-history me-2 text-primary"></i> Payment & Rollback History
        </h5>
        <span class="badge bg-label-info">{{ count($payments) }} Record(s)</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Mode</th>
                    <th class="text-end">Amount Paid</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Total Settled</th>
                    <th>Notes / Remarks</th>
                    <th>Processed By</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                @php
                    $isRollback = $pay->type === 'rollback';
                    $disc = (float)($pay->discount ?? 0);
                    $amt = (float)$pay->amount;
                    $totalSettled = $amt + $disc;
                @endphp
                <tr>
                    <td>{{ $pay->created_at ? $pay->created_at->format('d-m-Y h:i A') : $pay->payment_date->format('d-m-Y') }}</td>
                    <td>
                        @if($isRollback)
                            <span class="badge bg-label-danger"><i class="bx bx-undo me-1"></i> Rollback / Reversal</span>
                        @else
                            <span class="badge bg-label-success"><i class="bx bx-check me-1"></i> Payment Received</span>
                        @endif
                    </td>
                    <td>{{ $pay->payment_mode ?? 'Cash' }}</td>
                    <td class="text-end fw-bold {{ $amt < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $amt < 0 ? '-₹' . number_format(abs($amt), 2) : '₹' . number_format($amt, 2) }}
                    </td>
                    <td class="text-end">
                        @if($disc != 0)
                            <span class="badge bg-label-warning fw-bold">₹{{ number_format(abs($disc), 2) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold {{ $totalSettled < 0 ? 'text-danger' : 'text-primary' }}">
                        {{ $totalSettled < 0 ? '-₹' . number_format(abs($totalSettled), 2) : '₹' . number_format($totalSettled, 2) }}
                    </td>
                    <td style="max-width: 250px; white-space: normal;">
                        @if($isRollback)
                            <span class="text-danger fw-semibold"><i class="bx bx-info-circle me-1"></i> Reason: {{ $pay->rollback_reason }}</span>
                        @elseif(!empty($pay->notes))
                            <span class="text-dark"><i class="bx bx-note me-1 text-primary"></i> <span class="payment-notes-text-{{ $pay->id }}">{{ $pay->notes }}</span></span>
                        @else
                            <span class="text-muted fst-italic payment-notes-text-{{ $pay->id }}">-</span>
                        @endif
                    </td>
                    <td>{{ $pay->creator->name ?? 'System' }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            @if(!$isRollback)
                                <a href="{{ route('admin.payment-transactions.print-receipt', $pay->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Print Official Receipt">
                                    <i class="bx bx-printer"></i> Receipt
                                </a>

                                <button type="button" class="btn btn-xs btn-outline-info btn-edit-notes-modal" 
                                        data-payment-id="{{ $pay->id }}" 
                                        data-notes="{{ $pay->notes }}" 
                                        data-url="{{ route('admin.payment-transactions.update-notes', $pay->id) }}"
                                        title="View / Edit Payment Notes">
                                    <i class="bx bx-edit-alt"></i> Notes
                                </button>
                            @endif

                            @if(!$isRollback && !$pay->isRolledBack())
                                <button type="button" class="btn btn-xs btn-outline-danger btn-rollback-modal" 
                                        data-payment-id="{{ $pay->id }}" 
                                        data-amount="{{ number_format($pay->amount, 2) }}"
                                        data-url="{{ route($rollbackRoute, [$billId, $pay->id]) }}"
                                        title="Rollback Payment">
                                    <i class="bx bx-undo"></i> Rollback
                                </button>
                            @elseif(!$isRollback && $pay->isRolledBack())
                                <span class="badge bg-label-secondary"><i class="bx bx-x me-1"></i> Rolled Back</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-3 text-muted">No individual payment logs recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Payment Notes Modal -->
<div class="modal fade" id="editPaymentNotesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editPaymentNotesForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bx bx-edit me-2"></i> Edit Payment Notes & Remarks
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Remarks / Notes</label>
                        <textarea name="notes" id="edit_modal_notes" class="form-control" rows="4" placeholder="Enter remarks, cheque/online ref no, settlement details..."></textarea>
                        <small class="text-muted">These notes will be displayed in transaction history, payment receipts, and customer ledgers.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSavePaymentNotes">
                        <i class="bx bx-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rollback Confirmation Modal -->
<div class="modal fade" id="rollbackPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="rollbackForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bx bx-error-circle me-2"></i> Confirm Payment Rollback
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">
                        Are you sure you want to reverse/cancel this payment of 
                        <strong class="text-danger fs-5" id="rollbackAmountDisplay">₹0.00</strong>?
                    </p>
                    <p class="small text-warning bg-label-warning p-2 rounded">
                        <i class="bx bx-warning me-1"></i> Rolling back this payment will automatically restore the paid amount and any discount granted, increase the party's outstanding balance, update ledger history, and record an audit log.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Rollback / Cancellation <span class="text-danger">*</span></label>
                        <input type="text" name="rollback_reason" id="rollback_reason" class="form-control" placeholder="e.g. Wrong entry, Cheque bounced, Customer refund..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm & Rollback Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Notes Modal
    $(document).on('click', '.btn-edit-notes-modal', function() {
        var paymentId = $(this).data('payment-id');
        var notes = $(this).data('notes') || '';
        var url = $(this).data('url');

        $('#edit_modal_notes').val(notes);
        $('#editPaymentNotesForm').attr('action', url);
        $('#editPaymentNotesForm').data('payment-id', paymentId);
        $('#editPaymentNotesModal').modal('show');
    });

    $('#editPaymentNotesForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var paymentId = form.data('payment-id');
        var btn = $('#btnSavePaymentNotes');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Changes');
                if (res.success) {
                    $('#editPaymentNotesModal').modal('hide');
                    var updatedNotes = res.notes || '-';
                    $('.payment-notes-text-' + paymentId).text(updatedNotes);
                    // Update button data-notes attribute
                    $('.btn-edit-notes-modal[data-payment-id="' + paymentId + '"]').data('notes', res.notes || '');
                    if (typeof setFlash === 'function') {
                        setFlash('success', res.message || 'Payment notes updated successfully.');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('Success', res.message || 'Payment notes updated successfully.', 'success');
                    } else {
                        alert(res.message || 'Payment notes updated successfully.');
                    }
                } else {
                    alert(res.message || 'Error updating notes.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Changes');
                alert(xhr.responseJSON?.message || 'Server error updating notes.');
            }
        });
    });

    // Rollback Modal
    $(document).on('click', '.btn-rollback-modal', function() {
        var url = $(this).data('url');
        var amount = $(this).data('amount');
        $('#rollbackAmountDisplay').text('₹' + amount);
        $('#rollbackForm').attr('action', url);
        $('#rollbackPaymentModal').modal('show');
    });

    $('#rollbackForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var actionUrl = form.attr('action');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res.success) {
                    $('#rollbackPaymentModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message || 'Error executing rollback.');
                }
            },
            error: function(err) {
                alert('Server error executing payment rollback.');
            }
        });
    });
});
</script>
