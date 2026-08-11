<!-- Customer Ledger Modal -->
<div class="modal fade" id="customerLedgerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bx bx-history me-2"></i> Complete Customer Ledger History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="ledger_modal_loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted fw-semibold">Fetching customer transaction history...</p>
                </div>
                <div id="ledger_modal_content" style="display: none;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center border">
                                <small class="text-muted d-block fw-semibold text-uppercase">Total Amount Billed</small>
                                <h4 class="mb-0 text-primary fw-bold" id="modal_total_billed">₹0.00</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center border">
                                <small class="text-muted d-block fw-semibold text-uppercase">Total Paid / Deposited</small>
                                <h4 class="mb-0 text-success fw-bold" id="modal_total_paid">₹0.00</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center border">
                                <small class="text-muted d-block fw-semibold text-uppercase">Outstanding Balance</small>
                                <h4 class="mb-0 text-danger fw-bold" id="modal_outstanding">₹0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive border rounded">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Doc / Ref No</th>
                                    <th>Mode</th>
                                    <th class="text-end">Billed (Debit)</th>
                                    <th class="text-end">Paid (Credit)</th>
                                    <th class="text-end">Running Balance</th>
                                    <th>Details / Notes</th>
                                </tr>
                            </thead>
                            <tbody id="modal_ledger_tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="#" id="modal_full_statement_link" target="_blank" class="btn btn-outline-primary me-auto">
                    <i class="bx bx-printer me-1"></i> Open Full Ledger Statement Page
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function fetchCustomerLedger(customerId, updatePreviousBalance = true) {
    if (!customerId) {
        $('#customer_ledger_card').slideUp();
        if ($('#previous_balance').length > 0) {
            $('#previous_balance').val('0.00').trigger('input');
        }
        return;
    }
    $('#ledger_modal_loading').show();
    $('#ledger_modal_content').hide();

    $.ajax({
        url: '{{ url('admin/customers') }}/' + customerId + '/ledger-api',
        type: 'GET',
        success: function(res) {
            if (res.success) {
                $('#ledger_cust_name').text(res.customer.name);
                $('#ledger_total_billed').text('₹' + Number(res.summary.total_billed).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#ledger_total_paid').text('₹' + Number(res.summary.total_paid).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#ledger_outstanding').text('₹' + Number(res.summary.outstanding_balance).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                
                if ($('#previous_balance').length > 0 && updatePreviousBalance) {
                    $('#previous_balance').val(Number(res.summary.outstanding_balance).toFixed(2)).trigger('input');
                }

                $('#customer_ledger_card').slideDown();

                // Populate Modal Data
                $('#modal_total_billed').text('₹' + Number(res.summary.total_billed).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#modal_total_paid').text('₹' + Number(res.summary.total_paid).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#modal_outstanding').text('₹' + Number(res.summary.outstanding_balance).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#modal_full_statement_link').attr('href', '{{ url('admin/customers') }}/' + customerId + '/ledger');

                var tbody = '';
                if (res.history && res.history.length > 0) {
                    res.history.forEach(function(item) {
                        var badgeClass = 'bg-label-info';
                        if (item.type === 'Vehicle Invoice') badgeClass = 'bg-label-primary';
                        if (item.type === 'Payment Received') badgeClass = 'bg-label-success';
                        if (item.type === 'Payment Rollback') badgeClass = 'bg-label-warning';

                        tbody += '<tr>' +
                            '<td>' + item.display_date + '</td>' +
                            '<td><span class="badge ' + badgeClass + '">' + item.type + '</span></td>' +
                            '<td class="fw-bold">' + item.doc_no + '</td>' +
                            '<td>' + (item.payment_mode || '-') + '</td>' +
                            '<td class="text-end text-primary fw-semibold">' + (item.debit > 0 ? '₹' + Number(item.debit).toLocaleString('en-IN', {minimumFractionDigits: 2}) : '-') + '</td>' +
                            '<td class="text-end text-success fw-semibold">' + (item.credit > 0 ? '₹' + Number(item.credit).toLocaleString('en-IN', {minimumFractionDigits: 2}) : '-') + '</td>' +
                            '<td class="text-end fw-bold ' + (item.running_balance > 0 ? 'text-danger' : 'text-success') + '">₹' + Number(item.running_balance).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                            '<td><small class="text-muted">' + item.notes + '</small></td>' +
                            '</tr>';
                    });
                } else {
                    tbody = '<tr><td colspan="8" class="text-center py-4 text-muted">No transaction history found for this customer.</td></tr>';
                }
                $('#modal_ledger_tbody').html(tbody);
                $('#ledger_modal_loading').hide();
                $('#ledger_modal_content').show();
            }
        }
    });
}

$(document).ready(function() {
    $('#customer_select').on('change', function() {
        var custId = $(this).val();
        fetchCustomerLedger(custId, true);
    });

    if ($('#customer_select').val()) {
        var isEditMode = ($('input[name="_method"]').val() || '').toUpperCase() === 'PUT' || window.location.pathname.indexOf('/edit') !== -1;
        fetchCustomerLedger($('#customer_select').val(), !isEditMode);
    }
});
</script>
