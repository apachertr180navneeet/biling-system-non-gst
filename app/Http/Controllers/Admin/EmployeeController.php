<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Employee::orderBy('full_name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('employee_code', 'like', $escapedSearch)
                  ->orWhere('first_name', 'like', $escapedSearch)
                  ->orWhere('last_name', 'like', $escapedSearch)
                  ->orWhere('full_name', 'like', $escapedSearch)
                  ->orWhere('email', 'like', $escapedSearch)
                  ->orWhere('phone', 'like', $escapedSearch)
                  ->orWhere('designation', 'like', $escapedSearch)
                  ->orWhere('department', 'like', $escapedSearch);
            });
        }

        $employees = $query->paginate(20);
        return view('admin.employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        // Suggest next code
        $lastEmp = Employee::withTrashed()->latest('id')->first();
        $nextId = $lastEmp ? ($lastEmp->id + 1) : 1;
        $suggestedCode = 'EMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        $users = User::orderBy('full_name')->get();

        return view('admin.employees.create', compact('suggestedCode', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'salary_type' => 'required|in:monthly,daily',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['full_name'] = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));

        Employee::create($validated);

        return redirect()->route('admin.employees.index')->withSuccess('Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        $users = User::orderBy('full_name')->get();
        return view('admin.employees.edit', compact('employee', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'salary_type' => 'required|in:monthly,daily',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['full_name'] = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));

        $employee->update($validated);

        return redirect()->route('admin.employees.index')->withSuccess('Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(['success' => true, 'message' => 'Employee deleted successfully.']);
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        return response()->json(['success' => true, 'is_active' => $employee->fresh()->is_active]);
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = Employee::orderBy('full_name');

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('employee_code', 'like', $escapedSearch)
                  ->orWhere('first_name', 'like', $escapedSearch)
                  ->orWhere('last_name', 'like', $escapedSearch)
                  ->orWhere('full_name', 'like', $escapedSearch)
                  ->orWhere('designation', 'like', $escapedSearch);
            });
        }

        $employees = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Employee Code');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Designation');
        $sheet->setCellValue('D1', 'Department');
        $sheet->setCellValue('E1', 'Phone');
        $sheet->setCellValue('F1', 'Email');
        $sheet->setCellValue('G1', 'Salary Type');
        $sheet->setCellValue('H1', 'Basic Salary');
        $sheet->setCellValue('I1', 'Status');

        $row = 2;
        foreach ($employees as $emp) {
            $sheet->setCellValue('A' . $row, $emp->employee_code);
            $sheet->setCellValue('B' . $row, $emp->full_name);
            $sheet->setCellValue('C' . $row, $emp->designation);
            $sheet->setCellValue('D' . $row, $emp->department);
            $sheet->setCellValue('E' . $row, $emp->phone);
            $sheet->setCellValue('F' . $row, $emp->email);
            $sheet->setCellValue('G' . $row, ucfirst($emp->salary_type));
            $sheet->setCellValue('H' . $row, number_format($emp->basic_salary, 2));
            $sheet->setCellValue('I' . $row, $emp->is_active ? 'Active' : 'Inactive');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/employees_export.xls');
        $writer->save($path);

        return response()->download($path, 'employees_' . date('Ymd_His') . '.xls')->deleteFileAfterSend(true);
    }
}
