<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaster;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class VehicleMasterController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleMaster::orderBy('variant_name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('variant_name', 'like', $escapedSearch)
                  ->orWhere('color_name', 'like', $escapedSearch)
                  ->orWhere('fuel_type', 'like', $escapedSearch)
                  ->orWhere('transmission', 'like', $escapedSearch)
                  ->orWhere('battery_type', 'like', $escapedSearch)
                  ->orWhere('battery_make', 'like', $escapedSearch);
            });
        }

        $vehicles = $query->paginate(20);
        return view('admin.vehicle_masters.index', compact('vehicles', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = VehicleMaster::orderBy('variant_name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function($q) use ($escapedSearch) {
                $q->where('variant_name', 'like', $escapedSearch)
                  ->orWhere('color_name', 'like', $escapedSearch)
                  ->orWhere('fuel_type', 'like', $escapedSearch)
                  ->orWhere('transmission', 'like', $escapedSearch)
                  ->orWhere('battery_type', 'like', $escapedSearch)
                  ->orWhere('battery_make', 'like', $escapedSearch);
            });
        }

        $vehicles = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Variant Name');
        $sheet->setCellValue('B1', 'Color Name');
        $sheet->setCellValue('C1', 'Fuel Type');
        $sheet->setCellValue('D1', 'Transmission');
        $sheet->setCellValue('E1', 'Ex-Showroom Price');
        $sheet->setCellValue('F1', 'Battery Type');
        $sheet->setCellValue('G1', 'Battery Make');
        $sheet->setCellValue('H1', 'Min Stock');
        $sheet->setCellValue('I1', 'Status');

        $row = 2;
        foreach ($vehicles as $v) {
            $sheet->setCellValue('A' . $row, $v->variant_name);
            $sheet->setCellValue('B' . $row, $v->color_name);
            $sheet->setCellValue('C' . $row, $v->fuel_type);
            $sheet->setCellValue('D' . $row, $v->transmission);
            $sheet->setCellValue('E' . $row, $v->ex_showroom_price);
            $sheet->setCellValue('F' . $row, $v->battery_type);
            $sheet->setCellValue('G' . $row, $v->battery_make);
            $sheet->setCellValue('H' . $row, $v->min_stock ?? 0);
            $sheet->setCellValue('I' . $row, $v->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_masters_export.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_masters_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }

    public function create()
    {
        return view('admin.vehicle_masters.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'variant_name' => 'nullable|string|max:255',
            'color_name' => 'nullable|string|max:255',
            'fuel_type' => 'nullable|string|max:255',
            'transmission' => 'nullable|string|max:255',
            'ex_showroom_price' => 'required|numeric|min:0',
            'battery_type' => 'nullable|string|max:255',
            'battery_make' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
        ]);
        $data['min_stock'] = $data['min_stock'] ?? 0;
        try {
            VehicleMaster::create($data);
            return redirect()->route('admin.vehicle-masters.index')->withSuccess('Vehicle master created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit(VehicleMaster $vehicleMaster)
    {
        return view('admin.vehicle_masters.edit', ['vehicle' => $vehicleMaster]);
    }

    public function update(Request $request, VehicleMaster $vehicleMaster)
    {
        $data = $request->validate([
            'variant_name' => 'nullable|string|max:255',
            'color_name' => 'nullable|string|max:255',
            'fuel_type' => 'nullable|string|max:255',
            'transmission' => 'nullable|string|max:255',
            'ex_showroom_price' => 'required|numeric|min:0',
            'battery_type' => 'nullable|string|max:255',
            'battery_make' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
        ]);
        $data['min_stock'] = $data['min_stock'] ?? 0;
        try {
            $vehicleMaster->update($data);
            return redirect()->route('admin.vehicle-masters.index')->withSuccess('Vehicle master updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function destroy(VehicleMaster $vehicleMaster)
    {
        $vehicleMaster->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
    }

    public function toggleStatus(VehicleMaster $vehicleMaster)
    {
        $vehicleMaster->update(['is_active' => !$vehicleMaster->is_active]);
        return response()->json(['success' => true, 'is_active' => $vehicleMaster->is_active]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'variant_name');
        $sheet->setCellValue('B1', 'color_name');
        $sheet->setCellValue('C1', 'fuel_type');
        $sheet->setCellValue('D1', 'transmission');
        $sheet->setCellValue('E1', 'ex_showroom_price');
        $sheet->setCellValue('F1', 'battery_type');
        $sheet->setCellValue('G1', 'battery_make');
        $sheet->setCellValue('H1', 'min_stock');
        // Example row
        $sheet->setCellValue('A2', 'ARZOO ECO LI');
        $sheet->setCellValue('B2', 'BLACK');
        $sheet->setCellValue('C2', 'Electric');
        $sheet->setCellValue('D2', 'Automatic');
        $sheet->setCellValue('E2', '166666.00');
        $sheet->setCellValue('F2', 'LITHIUM');
        $sheet->setCellValue('G2', 'LITHIUM');
        $sheet->setCellValue('H2', '2');

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/vehicle_master_template.xls');
        $writer->save($path);

        return response()->download($path, 'vehicle_master_template.xls')->deleteFileAfterSend(true);
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

        $required = ['variant_name', 'color_name', 'fuel_type', 'transmission', 'ex_showroom_price'];
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
            
            $variantName = isset($data['variant_name']) ? trim($data['variant_name']) : '';
            $colorName = isset($data['color_name']) ? trim($data['color_name']) : '';
            $fuelType = isset($data['fuel_type']) ? trim($data['fuel_type']) : '';
            $transmission = isset($data['transmission']) ? trim($data['transmission']) : '';
            $exShowroomPrice = isset($data['ex_showroom_price']) ? trim($data['ex_showroom_price']) : '0';

            $batteryType = isset($data['battery_type']) ? trim($data['battery_type']) : '';
            $batteryMake = isset($data['battery_make']) ? trim($data['battery_make']) : '';
            $minStock = isset($data['min_stock']) && is_numeric(trim($data['min_stock'])) ? (int)trim($data['min_stock']) : 0;

            if (empty($variantName)) {
                $errors[] = "Row {$rowCount}: Variant Name is required.";
                $skipped++;
                continue;
            }

            if (!is_numeric($exShowroomPrice) || floatval($exShowroomPrice) < 0) {
                $errors[] = "Row {$rowCount}: Ex-Showroom Price must be a positive number.";
                $skipped++;
                continue;
            }

            $combKey = strtolower($variantName) . '|' . strtolower($colorName);

            if (in_array($combKey, $seenInFile)) {
                $errors[] = "Row {$rowCount}: Duplicate combination '{$variantName}' and '{$colorName}' in the CSV file.";
                $skipped++;
                continue;
            }

            $exists = VehicleMaster::whereRaw('LOWER(variant_name) = ?', [strtolower($variantName)])
                ->whereRaw('LOWER(color_name) = ?', [strtolower($colorName)])
                ->exists();

            if ($exists) {
                $errors[] = "Row {$rowCount}: Duplicate combination '{$variantName}' and '{$colorName}' already exists in the database.";
                $skipped++;
                continue;
            }

            $seenInFile[] = $combKey;

            VehicleMaster::create([
                'variant_name' => $variantName,
                'color_name' => $colorName ?: null,
                'fuel_type' => $fuelType ?: null,
                'transmission' => $transmission ?: null,
                'ex_showroom_price' => floatval($exShowroomPrice),
                'battery_type' => $batteryType ?: null,
                'battery_make' => $batteryMake ?: null,
                'min_stock' => $minStock,
                'is_active' => true,
            ]);

            $imported++;
        }

        $msg = "Import complete. Successfully imported: {$imported} record(s). Skipped: {$skipped} record(s).";
        
        if (!empty($errors)) {
            return redirect()->route('admin.vehicle-masters.index')
                ->withSuccess($msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.vehicle-masters.index')->withSuccess($msg);
    }
}
