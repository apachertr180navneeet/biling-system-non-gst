<script>
$(function(){
    $(document).off('click', '.receive-payment-btn').on('click', '.receive-payment-btn', function(e){
        e.preventDefault();
        var btn = $(this);
        var url = btn.data('url');
        var balance = parseFloat(btn.data('balance')) || 0;
        var titleText = btn.data('title') || 'Receive Payment';
        var isPayMode = (titleText === 'Pay Amount');
        var amountLabel = isPayMode ? 'Amount Paid (₹)' : 'Amount Received (₹)';
        var actionIcon = isPayMode ? 'bx-credit-card' : 'bx-wallet';
        var themeColor = isPayMode ? '#dc2626' : '#059669';

        Swal.fire({
            title: `<div style="color: ${themeColor}; font-weight: 700; font-size: 20px;">
                        <i class="bx ${actionIcon} me-1"></i> ${titleText}
                    </div>`,
            html: `
                <div class="text-start p-1" style="font-size: 13px;">
                    <!-- Balance Banner -->
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3 py-2 px-3 rounded" style="background: #f8fafc;">
                        <span class="fw-semibold text-secondary"><i class="bx bx-info-circle me-1"></i> Outstanding Balance:</span>
                        <span class="fs-5 fw-bold" style="color: #dc2626;" id="swal_outstanding_display">₹${balance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    </div>

                    <!-- Discount Option -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                            <i class="bx bx-purchase-tag-alt me-1 text-warning"></i> Settlement Discount (₹)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">₹</span>
                            <input type="number" id="swal_payment_discount" class="form-control" placeholder="0.00" min="0" max="${balance}" step="0.01" value="0.00">
                        </div>
                        <small class="text-muted d-block mt-1">Discount granted to party (cannot exceed outstanding balance)</small>
                    </div>

                    <!-- Net Payable Calculation -->
                    <div class="card bg-light border-0 mb-3 shadow-none">
                        <div class="card-body p-2 d-flex justify-content-between align-items-center rounded" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                            <span class="small fw-bold text-success text-uppercase">
                                <i class="bx bx-calculator me-1"></i> Net Payable Amount:
                            </span>
                            <span class="fw-bold fs-6 text-success" id="swal_net_payable_display">
                                ₹${balance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </span>
                        </div>
                    </div>

                    <!-- Amount Received / Paid -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                            <i class="bx bx-money me-1" style="color: ${themeColor};"></i> ${amountLabel} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">₹</span>
                            <input type="number" id="swal_payment_amount" class="form-control fw-bold fs-6" style="color: ${themeColor};" placeholder="0.00" min="0" max="${balance}" step="0.01" value="${balance.toFixed(2)}">
                        </div>
                        <small class="text-muted d-block mt-1">Actual amount collected / paid in cash or bank</small>
                    </div>

                    <!-- Payment Mode -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                            <i class="bx bx-credit-card me-1 text-info"></i> Payment Mode
                        </label>
                        <select id="swal_payment_mode" class="form-select swal2-select-custom">
                            <option value="Cash" selected>Cash</option>
                            <option value="UPI / Online">UPI / Online</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Card">Debit / Credit Card</option>
                        </select>
                    </div>

                    <!-- Notes While Receiving Payment -->
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                            <i class="bx bx-note me-1 text-secondary"></i> Payment Notes / Remarks
                        </label>
                        <textarea id="swal_payment_notes" class="form-control" rows="2" placeholder="Optional remarks, cheque/transaction reference, remarks..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: isPayMode ? '<i class="bx bx-check-circle me-1"></i> Confirm Payment' : '<i class="bx bx-check-circle me-1"></i> Receive Payment',
            confirmButtonColor: themeColor,
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#64748b',
            showLoaderOnConfirm: true,
            didOpen: () => {
                var $discInput = $('#swal_payment_discount');
                var $amtInput = $('#swal_payment_amount');
                var $netDisplay = $('#swal_net_payable_display');

                function updateCalculations() {
                    var disc = parseFloat($discInput.val()) || 0;
                    if (disc < 0) {
                        disc = 0;
                        $discInput.val('0.00');
                    }
                    if (disc > balance) {
                        disc = balance;
                        $discInput.val(balance.toFixed(2));
                    }
                    var netPayable = Math.max(0, balance - disc);
                    $netDisplay.text('₹' + netPayable.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    
                    // Auto update the payment amount
                    $amtInput.val(netPayable.toFixed(2)).attr('max', netPayable);
                }

                $discInput.on('input keyup change', updateCalculations);
            },
            preConfirm: () => {
                var amount = parseFloat($('#swal_payment_amount').val()) || 0;
                var discount = parseFloat($('#swal_payment_discount').val()) || 0;
                var paymentMode = $('#swal_payment_mode').val() || 'Cash';
                var notes = $('#swal_payment_notes').val() || '';

                if (discount < 0) {
                    Swal.showValidationMessage('Discount cannot be negative.');
                    return false;
                }
                if (discount > balance) {
                    Swal.showValidationMessage(`Discount (₹${discount.toFixed(2)}) cannot exceed outstanding balance of ₹${balance.toFixed(2)}.`);
                    return false;
                }
                if (amount <= 0 && discount <= 0) {
                    Swal.showValidationMessage('Please enter a valid amount or discount.');
                    return false;
                }
                var totalSettled = amount + discount;
                if (totalSettled > (balance + 0.001)) {
                    Swal.showValidationMessage(`Total settled (Received: ₹${amount.toFixed(2)} + Discount: ₹${discount.toFixed(2)} = ₹${totalSettled.toFixed(2)}) cannot exceed outstanding balance of ₹${balance.toFixed(2)}.`);
                    return false;
                }

                return $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        amount: amount,
                        discount: discount,
                        payment_mode: paymentMode,
                        notes: notes
                    }
                }).done(function(r) {
                    if (!r.success) {
                        Swal.showValidationMessage(r.message || 'Payment processing failed.');
                    }
                    return r;
                }).fail(function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Server error processing payment.';
                    Swal.showValidationMessage(msg);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.value.message || 'Payment processed successfully!',
                    confirmButtonColor: themeColor
                }).then(() => {
                    location.reload();
                });
            }
        });
    });
});
</script>
