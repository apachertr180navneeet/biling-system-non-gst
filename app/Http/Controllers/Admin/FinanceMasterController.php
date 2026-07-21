<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceMaster;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class FinanceMasterController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = FinanceMaster::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('description', 'like', $escapedSearch);
            });
        }

        $financeMasters = $query->paginate(20);
        return view('admin.finance_masters.index', compact('financeMasters', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = FinanceMaster::orderBy('name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('description', 'like', $escapedSearch);
            });
        }

        $financeMasters = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Description');
        $sheet->setCellValue('C1', 'Status');

        $row = 2;
        foreach ($financeMasters as $f) {
            $sheet->setCellValue('A' . $row, $f->name);
            $sheet->setCellValue('B' . $row, $f->description);
            $sheet->setCellValue('C' . $row, $f->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/finance_masters_export.xls');
        $writer->save($path);

        return response()->download($path, 'finance_masters_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        return view('admin.finance_masters.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        try {
            $financeMaster = FinanceMaster::create($data);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'finance_master' => $financeMaster
                ]);
            }
            return redirect()->route('admin.finance-masters.index')->withSuccess('Finance Master created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show(FinanceMaster $financeMaster)
    {
        return view('admin.finance_masters.show', compact('financeMaster'));
    }

    public function edit(FinanceMaster $financeMaster)
    {
        return view('admin.finance_masters.edit', compact('financeMaster'));
    }

    public function update(Request $request, FinanceMaster $financeMaster)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        try {
            $financeMaster->update($data);
            return redirect()->route('admin.finance-masters.index')->withSuccess('Finance Master updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function destroy(FinanceMaster $financeMaster)
    {
        $financeMaster->delete();
        return response()->json(['success' => true, 'message' => 'Finance Master deleted successfully.']);
    }

    public function toggleStatus(FinanceMaster $financeMaster)
    {
        $financeMaster->update(['is_active' => !$financeMaster->is_active]);
        return response()->json(['success' => true, 'is_active' => $financeMaster->fresh()->is_active]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'name');
        $sheet->setCellValue('B1', 'description');

        $sheet->setCellValue('A2', 'Example Finance');
        $sheet->setCellValue('B2', 'This is an example finance master entry.');

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/finance_master_template.xls');
        $writer->save($path);

        return response()->download($path, 'finance_master_template.xls')->deleteFileAfterSend(true);
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
            $description = isset($data['description']) ? trim($data['description']) : '';

            if (empty($name)) {
                $errors[] = "Row {$rowCount}: Name is required.";
                $skipped++;
                continue;
            }

            $nameKey = strtolower($name);

            if (in_array($nameKey, $seenInFile)) {
                $errors[] = "Row {$rowCount}: Duplicate Name '{$name}' in the CSV file.";
                $skipped++;
                continue;
            }

            $exists = FinanceMaster::where('name', $name)->exists();

            if ($exists) {
                $errors[] = "Row {$rowCount}: Finance Master with Name '{$name}' already exists in the database.";
                $skipped++;
                continue;
            }

            $seenInFile[] = $nameKey;

            FinanceMaster::create([
                'name' => $name,
                'description' => $description ?: null,
                'is_active' => true,
            ]);

            $imported++;
        }

        $msg = "Import complete. Successfully imported: {$imported} record(s). Skipped: {$skipped} record(s).";

        if (!empty($errors)) {
            return redirect()->route('admin.finance-masters.index')
                ->withSuccess($msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.finance-masters.index')->withSuccess($msg);
    }
}
