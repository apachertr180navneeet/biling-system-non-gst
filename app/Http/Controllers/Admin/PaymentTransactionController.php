<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartSalesInvoice;
use App\Models\PaymentTransaction;
use App\Models\PurchaseOrder;
use App\Models\VehiclePurchaseOrder;
use App\Models\VehicleSalesInvoice;
use Illuminate\Http\Request;

class PaymentTransactionController extends Controller
{
    /**
     * Update remarks / notes for a specific payment transaction record.
     */
    public function updateNotes(Request $request, PaymentTransaction $paymentTransaction)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $paymentTransaction->notes = $request->input('notes');
        $paymentTransaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment notes updated successfully.',
            'notes' => $paymentTransaction->notes,
        ]);
    }

    /**
     * Return printable official payment receipt page.
     */
    public function printReceipt(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction->load('creator', 'customer', 'supplier');

        $bill = null;
        $billNumber = '-';
        if ($paymentTransaction->bill_type === 'vehicle_sales') {
            $bill = VehicleSalesInvoice::find($paymentTransaction->bill_id);
            $billNumber = $bill ? $bill->invoice_number : '-';
        } elseif ($paymentTransaction->bill_type === 'part_sales') {
            $bill = PartSalesInvoice::find($paymentTransaction->bill_id);
            $billNumber = $bill ? $bill->invoice_number : '-';
        } elseif ($paymentTransaction->bill_type === 'vehicle_purchase') {
            $bill = VehiclePurchaseOrder::find($paymentTransaction->bill_id);
            $billNumber = $bill ? $bill->po_number : '-';
        } elseif ($paymentTransaction->bill_type === 'part_purchase') {
            $bill = PurchaseOrder::find($paymentTransaction->bill_id);
            $billNumber = $bill ? $bill->order_number : '-';
        }

        return view('admin.payment_transactions.receipt', compact('paymentTransaction', 'bill', 'billNumber'));
    }
}
