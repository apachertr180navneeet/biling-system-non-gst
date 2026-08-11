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
                    <th class="text-end">Amount</th>
                    <th>Rollback Reason / Notes</th>
                    <th>Processed By</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                <tr>
                    <td>{{ $pay->created_at ? $pay->created_at->format('d-m-Y h:i A') : $pay->payment_date->format('d-m-Y') }}</td>
                    <td>
                        @if($pay->type === 'rollback')
                            <span class="badge bg-label-danger"><i class="bx bx-undo me-1"></i> Rollback / Reversal</span>
                        @else
                            <span class="badge bg-label-success"><i class="bx bx-check me-1"></i> Payment Received</span>
                        @endif
                    </td>
                    <td>{{ $pay->payment_mode }}</td>
                    <td class="text-end fw-bold {{ $pay->amount < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $pay->amount < 0 ? '-₹' . number_format(abs($pay->amount), 2) : '₹' . number_format($pay->amount, 2) }}
                    </td>
                    <td>
                        @if($pay->type === 'rollback')
                            <span class="text-danger fw-semibold"><i class="bx bx-info-circle me-1"></i> Reason: {{ $pay->rollback_reason }}</span>
                        @else
                            <span class="text-muted">Payment Received</span>
                        @endif
                    </td>
                    <td>{{ $pay->creator->name ?? 'System' }}</td>
                    <td class="text-center">
                        @if($pay->type === 'payment' && !$pay->isRolledBack())
                            <button type="button" class="btn btn-xs btn-outline-danger btn-rollback-modal" 
                                    data-payment-id="{{ $pay->id }}" 
                                    data-amount="{{ number_format($pay->amount, 2) }}"
                                    data-url="{{ route($rollbackRoute, [$billId, $pay->id]) }}">
                                <i class="bx bx-undo me-1"></i> Rollback Payment
                            </button>
                        @elseif($pay->type === 'payment' && $pay->isRolledBack())
                            <span class="badge bg-label-secondary"><i class="bx bx-x me-1"></i> Rolled Back</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-3 text-muted">No individual payment logs recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
                        <i class="bx bx-warning me-1"></i> Rolling back this payment will automatically deduct the paid amount, increase the party's outstanding balance, update ledger history, and record an audit log.
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
    $('.btn-rollback-modal').on('click', function() {
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
