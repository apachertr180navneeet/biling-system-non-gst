<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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
                  ->orWhere('gstin', 'like', $escapedSearch)
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
                  ->orWhere('gstin', 'like', $escapedSearch)
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
        $sheet->setCellValue('H1', 'GSTIN');
        $sheet->setCellValue('I1', 'PAN No');
        $sheet->setCellValue('J1', 'Aadhaar No');
        $sheet->setCellValue('K1', 'Status');

        $row = 2;
        foreach ($customers as $c) {
            $sheet->setCellValue('A' . $row, $c->type);
            $sheet->setCellValue('B' . $row, $c->name);
            $sheet->setCellValue('C' . $row, $c->company_name);
            $sheet->setCellValue('D' . $row, $c->phone);
            $sheet->setCellValue('E' . $row, $c->email);
            $sheet->setCellValue('F' . $row, $c->address);
            $sheet->setCellValue('G' . $row, $c->state);
            $sheet->setCellValue('H' . $row, $c->gstin);
            $sheet->setCellValue('I' . $row, $c->pan_no);
            $sheet->setCellValue('J' . $row, $c->aadhaar_no);
            $sheet->setCellValue('K' . $row, $c->is_active ? 'Active' : 'Inactive');
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
            'gstin' => 'nullable|string|max:15',
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

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
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
            'gstin' => 'nullable|string|max:15',
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
        $sheet->setCellValue('H1', 'gstin');
        $sheet->setCellValue('I1', 'pan_no');
        $sheet->setCellValue('J1', 'aadhaar_no');
        
        // Example row
        $sheet->setCellValue('A2', 'individual');
        $sheet->setCellValue('B2', 'John Doe');
        $sheet->setCellValue('C2', '');
        $sheet->setCellValue('D2', '9876543210');
        $sheet->setCellValue('E2', 'john@example.com');
        $sheet->setCellValue('F2', '456 Elm Street');
        $sheet->setCellValue('G2', 'Maharashtra');
        $sheet->setCellValue('H2', '');
        $sheet->setCellValue('I2', 'ABCDE1234F');
        $sheet->setCellValue('J2', '123456789012');

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
            $gstin = isset($data['gstin']) ? trim($data['gstin']) : '';
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
                'gstin' => $gstin ?: null,
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
}
