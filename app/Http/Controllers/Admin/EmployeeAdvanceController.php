<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $status = $request->input('status');
        $search = $request->input('search');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = EmployeeAdvance::with('employee', 'creator')->latest('advance_date')->latest('id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($fromDate) {
            $query->whereDate('advance_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('advance_date', '<=', $toDate);
        }
        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('advance_number', 'like', $escapedSearch)
                  ->orWhereHas('employee', function ($eq) use ($escapedSearch) {
                      $eq->where('full_name', 'like', $escapedSearch)
                        ->orWhere('employee_code', 'like', $escapedSearch);
                  });
            });
        }

        // Summary Calculations
        $baseQuery = clone $query;
        $totalAdvancesGiven = (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('amount');
        $totalAdvancesDeducted = (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('deducted_amount');
        $totalAdvancesOutstanding = (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('remaining_amount');

        $advances = $query->paginate(20)->withQueryString();
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();

        return view('admin.employee_advances.index', compact(
            'advances',
            'employees',
            'employeeId',
            'status',
            'search',
            'fromDate',
            'toDate',
            'totalAdvancesGiven',
            'totalAdvancesDeducted',
            'totalAdvancesOutstanding'
        ));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();
        $selectedEmployeeId = $request->input('employee_id');

        return view('admin.employee_advances.create', compact('employees', 'selectedEmployeeId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        $date = Carbon::parse($validated['advance_date']);
        $prefix = 'ADV-' . $date->format('Ym') . '-';
        $lastAdvance = EmployeeAdvance::where('advance_number', 'like', $prefix . '%')->latest('id')->first();
        $seq = $lastAdvance ? ((int) str_replace($prefix, '', $lastAdvance->advance_number) + 1) : 1;
        
        $validated['advance_number'] = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $validated['deducted_amount'] = 0.00;
        $validated['remaining_amount'] = $validated['amount'];
        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id();

        $advance = EmployeeAdvance::create($validated);

        return redirect()->route('admin.employee-advances.show', $advance->id)
            ->with('success', 'Employee advance record created successfully.');
    }

    public function show(EmployeeAdvance $employeeAdvance)
    {
        $employeeAdvance->load('employee', 'creator', 'salarySlipDeductions.salarySlip');
        return view('admin.employee_advances.show', compact('employeeAdvance'));
    }

    public function destroy(EmployeeAdvance $employeeAdvance)
    {
        if ($employeeAdvance->deducted_amount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete advance record because payments/deductions have already been processed against it.'
            ], 422);
        }

        $employeeAdvance->delete();
        return response()->json([
            'success' => true,
            'message' => 'Employee advance deleted successfully.'
        ]);
    }
}
