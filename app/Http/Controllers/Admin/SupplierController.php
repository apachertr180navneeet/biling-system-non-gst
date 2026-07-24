<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Supplier::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('contact_person', 'like', $escapedSearch)
                  ->orWhere('phone', 'like', $escapedSearch)
                  ->orWhere('email', 'like', $escapedSearch)
                  ->orWhere('gstin', 'like', $escapedSearch);
            });
        }

        $suppliers = $query->paginate(20);
        return view('admin.suppliers.index', compact('suppliers', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = Supplier::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('contact_person', 'like', $escapedSearch)
                  ->orWhere('phone', 'like', $escapedSearch)
                  ->orWhere('email', 'like', $escapedSearch)
                  ->orWhere('gstin', 'like', $escapedSearch);
            });
        }

        $suppliers = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'GSTIN');
        $sheet->setCellValue('C1', 'Address');
        $sheet->setCellValue('D1', 'Contact Person');
        $sheet->setCellValue('E1', 'Phone');
        $sheet->setCellValue('F1', 'Email');
        $sheet->setCellValue('G1', 'Status');

        $row = 2;
        foreach ($suppliers as $s) {
            $sheet->setCellValue('A' . $row, $s->name);
            $sheet->setCellValue('B' . $row, $s->gstin);
            $sheet->setCellValue('C' . $row, $s->address);
            $sheet->setCellValue('D' . $row, $s->contact_person);
            $sheet->setCellValue('E' . $row, $s->phone);
            $sheet->setCellValue('F' . $row, $s->email);
            $sheet->setCellValue('G' . $row, $s->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/suppliers_export.xls');
        $writer->save($path);

        return response()->download($path, 'suppliers_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|digits:10',
            'email' => 'nullable|email|max:255',
        ]);
        try {
            Supplier::create($data);
            return redirect()->route('admin.suppliers.index')->withSuccess('Supplier created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|digits:10',
            'email' => 'nullable|email|max:255',
        ]);
        try {
            $supplier->update($data);
            return redirect()->route('admin.suppliers.index')->withSuccess('Supplier updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted successfully.']);
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        return response()->json(['success' => true, 'is_active' => $supplier->fresh()->is_active]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'name');
        $sheet->setCellValue('B1', 'gstin');
        $sheet->setCellValue('C1', 'address');
        $sheet->setCellValue('D1', 'contact_person');
        $sheet->setCellValue('E1', 'phone');
        $sheet->setCellValue('F1', 'email');
        
        // Example row
        $sheet->setCellValue('A2', 'Supplier Inc');
        $sheet->setCellValue('B2', '27AAAAA1111A1Z1');
        $sheet->setCellValue('C2', '123 Main Street');
        $sheet->setCellValue('D2', 'John Doe');
        $sheet->setCellValue('E2', '9876543210');
        $sheet->setCellValue('F2', 'supplier@example.com');

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/supplier_template.xls');
        $writer->save($path);

        return response()->download($path, 'supplier_template.xls')->deleteFileAfterSend(true);
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

        $required = ['name'];
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
            
            $name = isset($data['name']) ? trim($data['name']) : '';
            $gstin = isset($data['gstin']) ? trim($data['gstin']) : '';
            $address = isset($data['address']) ? trim($data['address']) : '';
            $contactPerson = isset($data['contact_person']) ? trim($data['contact_person']) : '';
            $phone = isset($data['phone']) ? trim($data['phone']) : '';
            $email = isset($data['email']) ? trim($data['email']) : '';

            if (empty($name)) {
                $errors[] = "Row {$rowCount}: Name is required.";
                $skipped++;
                continue;
            }

            if (!empty($phone) && (!is_numeric($phone) || strlen($phone) !== 10)) {
                $errors[] = "Row {$rowCount}: Phone must be a 10-digit number.";
                $skipped++;
                continue;
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowCount}: Email format is invalid.";
                $skipped++;
                continue;
            }

            $nameKey = strtolower($name);

            if (in_array($nameKey, $seenInFile)) {
                $errors[] = "Row {$rowCount}: Duplicate Supplier name '{$name}' in the CSV file.";
                $skipped++;
                continue;
            }

            $exists = Supplier::whereRaw('LOWER(name) = ?', [$nameKey])->exists();

            if ($exists) {
                $errors[] = "Row {$rowCount}: Duplicate Supplier name '{$name}' already exists in the database.";
                $skipped++;
                continue;
            }

            $seenInFile[] = $nameKey;

            Supplier::create([
                'name' => $name,
                'gstin' => $gstin ?: null,
                'address' => $address ?: null,
                'contact_person' => $contactPerson ?: null,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'is_active' => true,
            ]);

            $imported++;
        }

        $msg = "Import complete. Successfully imported: {$imported} record(s). Skipped: {$skipped} record(s).";
        
        if (!empty($errors)) {
            return redirect()->route('admin.suppliers.index')
                ->withSuccess($msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.suppliers.index')->withSuccess($msg);
    }
}
