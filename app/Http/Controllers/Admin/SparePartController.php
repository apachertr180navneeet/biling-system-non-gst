<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = SparePart::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('part_no', 'like', $escapedSearch)
                  ->orWhere('name', 'like', $escapedSearch);
            });
        }

        $parts = $query->paginate(20);
        return view('admin.spare_parts.index', compact('parts', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = SparePart::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('part_no', 'like', $escapedSearch)
                  ->orWhere('name', 'like', $escapedSearch);
            });
        }

        $parts = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Part No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'MRP');
        $sheet->setCellValue('D1', 'Selling Price');
        $sheet->setCellValue('E1', 'Unit');
        $sheet->setCellValue('F1', 'Min Stock');
        $sheet->setCellValue('G1', 'Status');

        $row = 2;
        foreach ($parts as $p) {
            $sheet->setCellValue('A' . $row, $p->part_no);
            $sheet->setCellValue('B' . $row, $p->name);
            $sheet->setCellValue('C' . $row, $p->mrp);
            $sheet->setCellValue('D' . $row, $p->selling_price);
            $sheet->setCellValue('E' . $row, $p->unit);
            $sheet->setCellValue('F' . $row, $p->min_stock ?? 0);
            $sheet->setCellValue('G' . $row, $p->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/spare_parts_export.xls');
        $writer->save($path);

        return response()->download($path, 'spare_parts_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        return view('admin.spare_parts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'part_no' => 'required|string|max:100|unique:spare_parts',
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'unit' => 'required|string|max:10',
            'min_stock' => 'nullable|integer|min:0',
        ]);
        $data['min_stock'] = $data['min_stock'] ?? 0;
        try {
            SparePart::create($data);
            return redirect()->route('admin.spare-parts.index')->withSuccess('Spare part created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit(SparePart $sparePart)
    {
        return view('admin.spare_parts.edit', compact('sparePart'));
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $data = $request->validate([
            'part_no' => 'required|string|max:100|unique:spare_parts,part_no,' . $sparePart->id,
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'unit' => 'required|string|max:10',
            'min_stock' => 'nullable|integer|min:0',
        ]);
        $data['min_stock'] = $data['min_stock'] ?? 0;
        try {
            $sparePart->update($data);
            return redirect()->route('admin.spare-parts.index')->withSuccess('Spare part updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function destroy(SparePart $sparePart)
    {
        $sparePart->delete();
        return response()->json(['success' => true, 'message' => 'Spare part deleted successfully.']);
    }

    public function toggleStatus(SparePart $sparePart)
    {
        $sparePart->update(['is_active' => !$sparePart->is_active]);
        return response()->json(['success' => true, 'is_active' => $sparePart->fresh()->is_active]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'part_no');
        $sheet->setCellValue('B1', 'name');
        $sheet->setCellValue('C1', 'selling_price');
        $sheet->setCellValue('D1', 'mrp');
        $sheet->setCellValue('E1', 'unit');
        $sheet->setCellValue('F1', 'min_stock');
        
        // Example row
        $sheet->setCellValue('A2', 'PART-001');
        $sheet->setCellValue('B2', 'Engine Oil');
        $sheet->setCellValue('C2', '350.00');
        $sheet->setCellValue('D2', '400.00');
        $sheet->setCellValue('E2', 'Litre');
        $sheet->setCellValue('F2', '5');

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/spare_part_template.xls');
        $writer->save($path);

        return response()->download($path, 'spare_part_template.xls')->deleteFileAfterSend(true);
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

        $required = ['part_no', 'name', 'selling_price', 'mrp', 'unit'];
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
            
            $partNo = isset($data['part_no']) ? trim($data['part_no']) : '';
            $name = isset($data['name']) ? trim($data['name']) : '';
            $sellingPrice = isset($data['selling_price']) ? trim($data['selling_price']) : '0';
            $mrp = isset($data['mrp']) ? trim($data['mrp']) : '0';
            $unit = isset($data['unit']) ? trim($data['unit']) : '';
            $minStock = isset($data['min_stock']) && is_numeric(trim($data['min_stock'])) ? (int)trim($data['min_stock']) : 0;

            if (empty($partNo) || empty($name) || empty($unit)) {
                $errors[] = "Row {$rowCount}: Part No, Name and Unit are required.";
                $skipped++;
                continue;
            }

            if (!is_numeric($sellingPrice) || floatval($sellingPrice) < 0 || !is_numeric($mrp) || floatval($mrp) < 0) {
                $errors[] = "Row {$rowCount}: Prices must be positive numbers.";
                $skipped++;
                continue;
            }

            $partNoKey = strtolower($partNo);

            if (in_array($partNoKey, $seenInFile)) {
                $errors[] = "Row {$rowCount}: Duplicate Part No '{$partNo}' in the CSV file.";
                $skipped++;
                continue;
            }

            $exists = SparePart::whereRaw('LOWER(part_no) = ?', [$partNoKey])->exists();

            if ($exists) {
                $errors[] = "Row {$rowCount}: Duplicate Part No '{$partNo}' already exists in the database.";
                $skipped++;
                continue;
            }

            $seenInFile[] = $partNoKey;

            SparePart::create([
                'part_no' => $partNo,
                'name' => $name,
                'selling_price' => floatval($sellingPrice),
                'mrp' => floatval($mrp),
                'unit' => $unit,
                'min_stock' => $minStock,
                'is_active' => true,
            ]);

            $imported++;
        }

        $msg = "Import complete. Successfully imported: {$imported} record(s). Skipped: {$skipped} record(s).";
        
        if (!empty($errors)) {
            return redirect()->route('admin.spare-parts.index')
                ->withSuccess($msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.spare-parts.index')->withSuccess($msg);
    }
}
