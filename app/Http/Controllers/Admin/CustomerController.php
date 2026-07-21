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
        $query = Customer::orderBy('first_name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('first_name', 'like', $escapedSearch)
                  ->orWhere('last_name', 'like', $escapedSearch)
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
        $query = Customer::orderBy('first_name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('first_name', 'like', $escapedSearch)
                  ->orWhere('last_name', 'like', $escapedSearch)
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
        $sheet->setCellValue('B1', 'First Name');
        $sheet->setCellValue('C1', 'Last Name');
        $sheet->setCellValue('D1', 'Company Name');
        $sheet->setCellValue('E1', 'Phone');
        $sheet->setCellValue('F1', 'Email');
        $sheet->setCellValue('G1', 'Address');
        $sheet->setCellValue('H1', 'State');
        $sheet->setCellValue('I1', 'GSTIN');
        $sheet->setCellValue('J1', 'PAN No');
        $sheet->setCellValue('K1', 'Aadhaar No');
        $sheet->setCellValue('L1', 'Status');

        $row = 2;
        foreach ($customers as $c) {
            $sheet->setCellValue('A' . $row, $c->type);
            $sheet->setCellValue('B' . $row, $c->first_name);
            $sheet->setCellValue('C' . $row, $c->last_name);
            $sheet->setCellValue('D' . $row, $c->company_name);
            $sheet->setCellValue('E' . $row, $c->phone);
            $sheet->setCellValue('F' . $row, $c->email);
            $sheet->setCellValue('G' . $row, $c->address);
            $sheet->setCellValue('H' . $row, $c->state);
            $sheet->setCellValue('I' . $row, $c->gstin);
            $sheet->setCellValue('J' . $row, $c->pan_no);
            $sheet->setCellValue('K' . $row, $c->aadhaar_no);
            $sheet->setCellValue('L' . $row, $c->is_active ? 'Active' : 'Inactive');
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
            'type' => 'required|in:individual,corporate',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|digits:10',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:15',
            'pan_no' => 'nullable|string|max:10',
            'aadhaar_no' => 'nullable|string|max:12',
        ]);
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
            'type' => 'required|in:individual,corporate',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|digits:10',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:15',
            'pan_no' => 'nullable|string|max:10',
            'aadhaar_no' => 'nullable|string|max:12',
        ]);
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
        $sheet->setCellValue('B1', 'first_name');
        $sheet->setCellValue('C1', 'last_name');
        $sheet->setCellValue('D1', 'company_name');
        $sheet->setCellValue('E1', 'phone');
        $sheet->setCellValue('F1', 'email');
        $sheet->setCellValue('G1', 'address');
        $sheet->setCellValue('H1', 'state');
        $sheet->setCellValue('I1', 'gstin');
        $sheet->setCellValue('J1', 'pan_no');
        $sheet->setCellValue('K1', 'aadhaar_no');
        
        // Example row
        $sheet->setCellValue('A2', 'individual');
        $sheet->setCellValue('B2', 'John');
        $sheet->setCellValue('C2', 'Doe');
        $sheet->setCellValue('D2', '');
        $sheet->setCellValue('E2', '9876543210');
        $sheet->setCellValue('F2', 'john@example.com');
        $sheet->setCellValue('G2', '456 Elm Street');
        $sheet->setCellValue('H2', 'Maharashtra');
        $sheet->setCellValue('I2', '');
        $sheet->setCellValue('J2', 'ABCDE1234F');
        $sheet->setCellValue('K2', '123456789012');

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

        $required = ['type', 'first_name', 'last_name', 'phone'];
        foreach ($required as $req) {
            if (!in_array($req, $header)) {
                return redirect()->back()->withErrors(['csv_file' => "Missing required header column: {$req}"]);
            }
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowCount = 0;
        $seenInFile = [];

        foreach ($dataRows as $row) {
            $rowCount++;
            if (count($row) !== count($header)) {
                if (count(array_filter($row)) === 0) {
                    continue;
                }
                $errors[] = "Row {$rowCount}: Column count mismatch.";
                $skipped++;
                continue;
            }

            $data = array_combine($header, $row);
            
            $type = isset($data['type']) ? trim($data['type']) : '';
            $firstName = isset($data['first_name']) ? trim($data['first_name']) : '';
            $lastName = isset($data['last_name']) ? trim($data['last_name']) : '';
            $companyName = isset($data['company_name']) ? trim($data['company_name']) : '';
            $phone = isset($data['phone']) ? trim($data['phone']) : '';
            $email = isset($data['email']) ? trim($data['email']) : '';
            $address = isset($data['address']) ? trim($data['address']) : '';
            $state = isset($data['state']) ? trim($data['state']) : '';
            $gstin = isset($data['gstin']) ? trim($data['gstin']) : '';
            $panNo = isset($data['pan_no']) ? trim($data['pan_no']) : '';
            $aadhaarNo = isset($data['aadhaar_no']) ? trim($data['aadhaar_no']) : '';

            if (empty($type) || empty($firstName) || empty($lastName) || empty($phone)) {
                $errors[] = "Row {$rowCount}: Type, First Name, Last Name and Phone are required.";
                $skipped++;
                continue;
            }

            if (!in_array($type, ['individual', 'corporate'])) {
                $errors[] = "Row {$rowCount}: Type must be 'individual' or 'corporate'.";
                $skipped++;
                continue;
            }

            if (!is_numeric($phone) || strlen($phone) !== 10) {
                $errors[] = "Row {$rowCount}: Phone must be a 10-digit number.";
                $skipped++;
                continue;
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowCount}: Email format is invalid.";
                $skipped++;
                continue;
            }

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

            Customer::create([
                'type' => $type,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'company_name' => $companyName ?: null,
                'phone' => $phone,
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
