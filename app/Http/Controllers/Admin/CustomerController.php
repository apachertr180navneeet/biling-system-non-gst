<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\VehicleSalesInvoice;
use App\Models\PartSalesInvoice;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Customer::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('phone', 'like', $escapedSearch)
                  ->orWhere('email', 'like', $escapedSearch)
                  ->orWhere('company_name', 'like', $escapedSearch);
            });
        }

        $customers = $query->paginate(20);
        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = Customer::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('phone', 'like', $escapedSearch)
                  ->orWhere('email', 'like', $escapedSearch)
                  ->orWhere('company_name', 'like', $escapedSearch);
            });
        }

        $customers = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Type');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Company Name');
        $sheet->setCellValue('D1', 'Phone');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'State');
        $sheet->setCellValue('H1', 'PAN No');
        $sheet->setCellValue('I1', 'Aadhaar No');
        $sheet->setCellValue('J1', 'Status');

        $row = 2;
        foreach ($customers as $c) {
            $sheet->setCellValue('A' . $row, $c->type);
            $sheet->setCellValue('B' . $row, $c->name);
            $sheet->setCellValue('C' . $row, $c->company_name);
            $sheet->setCellValue('D' . $row, $c->phone);
            $sheet->setCellValue('E' . $row, $c->email);
            $sheet->setCellValue('F' . $row, $c->address);
            $sheet->setCellValue('G' . $row, $c->state);
            $sheet->setCellValue('H' . $row, $c->pan_no);
            $sheet->setCellValue('I' . $row, $c->aadhaar_no);
            $sheet->setCellValue('J' . $row, $c->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/customers_export.xls');
        $writer->save($path);

        return response()->download($path, 'customers_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'nullable|in:individual,corporate',
            'name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'pan_no' => 'nullable|string|max:10',
            'aadhaar_no' => 'nullable|string|max:12',
        ]);

        $data['type'] = $data['type'] ?? 'individual';
        $data['name'] = $data['name'] ?? ($data['company_name'] ?? 'Customer');
        $data['phone'] = !empty($data['phone']) ? $data['phone'] : null;

        try {
            $customer = Customer::create($data);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'customer' => $customer
                ]);
            }
            return redirect()->route('admin.customers.index')->withSuccess('Customer created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Customer $customer)
    {
        $apiResponse = $this->ledgerApi($request, $customer);
        $data = $apiResponse->getData(true);

        return view('admin.customers.show', [
            'customer' => $customer,
            'summary' => $data['summary'] ?? [],
            'history' => $data['history'] ?? [],
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'type' => 'nullable|in:individual,corporate',
            'name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'pan_no' => 'nullable|string|max:10',
            'aadhaar_no' => 'nullable|string|max:12',
        ]);

        $data['type'] = $data['type'] ?? 'individual';
        $data['name'] = $data['name'] ?? ($data['company_name'] ?? 'Customer');
        $data['phone'] = !empty($data['phone']) ? $data['phone'] : null;

        try {
            $customer->update($data);
            return redirect()->route('admin.customers.index')->withSuccess('Customer updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        return response()->json(['success' => true, 'is_active' => $customer->fresh()->is_active]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'type');
        $sheet->setCellValue('B1', 'name');
        $sheet->setCellValue('C1', 'company_name');
        $sheet->setCellValue('D1', 'phone');
        $sheet->setCellValue('E1', 'email');
        $sheet->setCellValue('F1', 'address');
        $sheet->setCellValue('G1', 'state');
        $sheet->setCellValue('H1', 'pan_no');
        $sheet->setCellValue('I1', 'aadhaar_no');
        
        // Example row
        $sheet->setCellValue('A2', 'individual');
        $sheet->setCellValue('B2', 'John Doe');
        $sheet->setCellValue('C2', '');
        $sheet->setCellValue('D2', '9876543210');
        $sheet->setCellValue('E2', 'john@example.com');
        $sheet->setCellValue('F2', '456 Elm Street');
        $sheet->setCellValue('G2', 'Maharashtra');
        $sheet->setCellValue('H2', 'ABCDE1234F');
        $sheet->setCellValue('I2', '123456789012');

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/customer_template.xls');
        $writer->save($path);

        return response()->download($path, 'customer_template.xls')->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:2048',
        ]);

        $file = $request->file('csv_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xls', 'xlsx'])) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            if (count($rows) < 2) {
                return redirect()->back()->withErrors(['csv_file' => 'The uploaded file is empty.']);
            }
            $header = array_map(function($h) {
                return trim(strtolower($h));
            }, array_shift($rows));
            $dataRows = $rows;
        } else {
            $handle = fopen($file->getRealPath(), 'r');
            if (!$handle) {
                return redirect()->back()->withErrors(['csv_file' => 'Failed to open the uploaded file.']);
            }
            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return redirect()->back()->withErrors(['csv_file' => 'The uploaded file is empty.']);
            }
            $header = array_map(function($h) {
                return trim(strtolower($h));
            }, $header);
            $dataRows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $dataRows[] = $row;
            }
            fclose($handle);
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowCount = 0;
        $seenInFile = [];

        foreach ($dataRows as $row) {
            $rowCount++;
            if (count(array_filter($row)) === 0) {
                continue;
            }
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            } elseif (count($row) > count($header)) {
                $row = array_slice($row, 0, count($header));
            }

            $data = array_combine($header, $row);
            
            $type = isset($data['type']) && !empty(trim($data['type'])) ? trim($data['type']) : 'individual';
            $name = isset($data['name']) ? trim($data['name']) : '';
            if (empty($name) && isset($data['first_name'])) {
                $name = trim($data['first_name'] . ' ' . ($data['last_name'] ?? ''));
            }
            $companyName = isset($data['company_name']) ? trim($data['company_name']) : '';
            if (empty($name)) {
                $name = $companyName ?: 'Customer';
            }
            $phone = isset($data['phone']) ? trim($data['phone']) : '';
            $email = isset($data['email']) ? trim($data['email']) : '';
            $address = isset($data['address']) ? trim($data['address']) : '';
            $state = isset($data['state']) ? trim($data['state']) : '';
            $panNo = isset($data['pan_no']) ? trim($data['pan_no']) : '';
            $aadhaarNo = isset($data['aadhaar_no']) ? trim($data['aadhaar_no']) : '';

            if (!empty($phone)) {
                $phoneKey = strtolower($phone);
                if (in_array($phoneKey, $seenInFile)) {
                    $errors[] = "Row {$rowCount}: Duplicate Phone '{$phone}' in the CSV file.";
                    $skipped++;
                    continue;
                }
                $exists = Customer::where('phone', $phone)->exists();
                if ($exists) {
                    $errors[] = "Row {$rowCount}: Customer with Phone '{$phone}' already exists in the database.";
                    $skipped++;
                    continue;
                }
                $seenInFile[] = $phoneKey;
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowCount}: Email format is invalid.";
                $skipped++;
                continue;
            }

            Customer::create([
                'type' => in_array($type, ['individual', 'corporate']) ? $type : 'individual',
                'name' => $name,
                'company_name' => $companyName ?: null,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'address' => $address ?: null,
                'state' => $state ?: null,
                'pan_no' => $panNo ?: null,
                'aadhaar_no' => $aadhaarNo ?: null,
                'is_active' => true,
            ]);

            $imported++;
        }

        $msg = "Import complete. Successfully imported: {$imported} record(s). Skipped: {$skipped} record(s).";
        
        if (!empty($errors)) {
            return redirect()->route('admin.customers.index')
                ->withSuccess($msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.customers.index')->withSuccess($msg);
    }

    public function ledgerApi(Request $request, Customer $customer)
    {
        $vehicleInvoices = VehicleSalesInvoice::where(function($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if (!empty($customer->name)) {
                    $q->orWhere(function($subQ) use ($customer) {
                        $subQ->whereNull('customer_id')->where('customer_name', $customer->name);
                    });
                }
            })
            ->orderBy('invoice_date', 'asc')
            ->get();

        $partInvoices = PartSalesInvoice::where(function($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if (!empty($customer->name)) {
                    $q->orWhere(function($subQ) use ($customer) {
                        $subQ->whereNull('customer_id')->where('customer_name', $customer->name);
                    });
                }
            })
            ->orderBy('invoice_date', 'asc')
            ->get();

        $payments = PaymentTransaction::where('party_type', 'customer')
            ->where(function($q) use ($customer) {
                $q->where('party_id', $customer->id);
                if (!empty($customer->name)) {
                    $q->orWhere(function($subQ) use ($customer) {
                        $subQ->whereNull('party_id')->where('party_name', $customer->name);
                    });
                }
            })
            ->orderBy('payment_date', 'asc')
            ->get();

        $totalBilled = 0;
        $totalPaid = 0;

        foreach ($vehicleInvoices as $v) {
            $totalBilled += (float)($v->grand_total ?? 0);
            $totalPaid += (float)($v->received_amount ?? 0);
        }

        foreach ($partInvoices as $p) {
            $totalBilled += (float)($p->total_amount ?? 0);
            $totalPaid += (float)($p->received_amount ?? 0);
        }

        $outstandingBalance = $totalBilled - $totalPaid;

        $history = [];

        foreach ($vehicleInvoices as $v) {
            $vDate = !empty($v->invoice_date) ? (is_a($v->invoice_date, '\DateTimeInterface') ? $v->invoice_date : \Carbon\Carbon::parse($v->invoice_date)) : null;
            $history[] = [
                'date' => $vDate ? $vDate->format('Y-m-d') : '',
                'display_date' => $vDate ? $vDate->format('d-m-Y') : '',
                'type' => 'Vehicle Invoice',
                'doc_no' => $v->invoice_number ?? '',
                'debit' => (float)($v->grand_total ?? 0),
                'credit' => 0,
                'received' => (float)($v->received_amount ?? 0),
                'balance' => (float)($v->balance ?? 0),
                'payment_mode' => $v->payment_mode ?? '',
                'notes' => 'Vehicle Invoice #' . ($v->invoice_number ?? ''),
                'view_url' => route('admin.vehicle-sales-invoices.show', $v->id),
            ];
        }

        foreach ($partInvoices as $p) {
            $pDate = !empty($p->invoice_date) ? (is_a($p->invoice_date, '\DateTimeInterface') ? $p->invoice_date : \Carbon\Carbon::parse($p->invoice_date)) : null;
            $history[] = [
                'date' => $pDate ? $pDate->format('Y-m-d') : '',
                'display_date' => $pDate ? $pDate->format('d-m-Y') : '',
                'type' => 'Part Invoice',
                'doc_no' => $p->invoice_number ?? '',
                'debit' => (float)($p->total_amount ?? 0),
                'credit' => 0,
                'received' => (float)($p->received_amount ?? 0),
                'balance' => (float)($p->balance ?? 0),
                'payment_mode' => $p->payment_mode ?? '',
                'notes' => 'Part Invoice #' . ($p->invoice_number ?? ''),
                'view_url' => route('admin.part-sales-invoices.show', $p->id),
            ];
        }

        foreach ($payments as $pay) {
            $payDate = !empty($pay->payment_date) ? (is_a($pay->payment_date, '\DateTimeInterface') ? $pay->payment_date : \Carbon\Carbon::parse($pay->payment_date)) : null;
            $history[] = [
                'date' => $payDate ? $payDate->format('Y-m-d') : '',
                'display_date' => $payDate ? $payDate->format('d-m-Y') : '',
                'type' => $pay->type === 'rollback' ? 'Payment Rollback' : 'Payment Received',
                'doc_no' => 'PAY-' . $pay->id,
                'debit' => $pay->type === 'rollback' ? (float)abs($pay->amount ?? 0) : 0,
                'credit' => $pay->type === 'rollback' ? 0 : (float)($pay->amount ?? 0),
                'received' => (float)($pay->amount ?? 0),
                'balance' => 0,
                'payment_mode' => $pay->payment_mode ?? '',
                'notes' => $pay->type === 'rollback' ? 'Rollback: ' . ($pay->rollback_reason ?? 'Reversal') : 'Payment via ' . ($pay->payment_mode ?? 'Cash'),
                'view_url' => '#',
            ];
        }

        usort($history, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $runningBal = 0;
        foreach ($history as &$item) {
            $runningBal += ($item['debit'] - $item['credit']);
            $item['running_balance'] = $runningBal;
        }

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'company_name' => $customer->company_name,
            ],
            'summary' => [
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'outstanding_balance' => $outstandingBalance,
                'total_invoices' => count($vehicleInvoices) + count($partInvoices),
            ],
            'history' => array_reverse($history),
        ]);
    }

    public function showLedger(Request $request, Customer $customer)
    {
        $apiResponse = $this->ledgerApi($request, $customer);
        $data = $apiResponse->getData(true);

        return view('admin.customers.ledger', [
            'customer' => $customer,
            'summary' => $data['summary'],
            'history' => $data['history'],
        ]);
    }
}

