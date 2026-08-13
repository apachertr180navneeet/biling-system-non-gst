<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\SalarySlip;
use App\Models\SalarySlipAdvanceDeduction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalarySlipController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $search = $request->input('search');

        $query = SalarySlip::with('employee')->latest();

        if ($month) {
            $query->where('month', $month);
        }
        if ($year) {
            $query->where('year', $year);
        }
        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->whereHas('employee', function ($q) use ($escapedSearch) {
                $q->where('full_name', 'like', $escapedSearch)
                  ->orWhere('employee_code', 'like', $escapedSearch);
            });
        }

        $salarySlips = $query->paginate(20);
        return view('admin.salary_slips.index', compact('salarySlips', 'month', 'year', 'search'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();
        $selectedEmployeeId = $request->input('employee_id');
        $selectedMonth = (int) $request->input('month', Carbon::now()->month);
        $selectedYear = (int) $request->input('year', Carbon::now()->year);

        return view('admin.salary_slips.create', compact('employees', 'selectedEmployeeId', 'selectedMonth', 'selectedYear'));
    }

    public function calculateApi(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $month = (int) $request->input('month');
        $year = (int) $request->input('year');

        if (!$employeeId || !$month || !$year) {
            return response()->json(['success' => false, 'message' => 'Missing parameters.'], 400);
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $totalDays = $startDate->daysInMonth;

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $halfDays = $attendances->where('status', 'half_day')->count();
        $paidLeaves = $attendances->where('status', 'leave')->count();

        // Effective days = present + (half_days * 0.5) + paid_leaves
        $effectiveDays = $presentDays + ($halfDays * 0.5) + $paidLeaves;

        // If no attendance records were marked for the month at all, default to full month (or 0 if un-marked)
        if ($attendances->count() == 0) {
            $effectiveDays = $totalDays;
            $presentDays = $totalDays;
        }

        $basicSalary = (float) $employee->basic_salary;
        if ($employee->salary_type === 'daily') {
            $earnedSalary = round($basicSalary * $effectiveDays, 2);
        } else {
            // Monthly salary
            $perDayRate = $totalDays > 0 ? ($basicSalary / $totalDays) : 0;
            $earnedSalary = round($perDayRate * $effectiveDays, 2);
        }

        $outstandingAdvance = $employee->outstandingAdvanceBalance();

        return response()->json([
            'success' => true,
            'data' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'half_days' => $halfDays,
                'paid_leaves' => $paidLeaves,
                'effective_days' => $effectiveDays,
                'salary_type' => $employee->salary_type,
                'basic_salary' => $basicSalary,
                'earned_salary' => $earnedSalary,
                'outstanding_advance' => $outstandingAdvance,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2099',
            'total_days' => 'required|integer|min:1',
            'present_days' => 'required|numeric|min:0',
            'absent_days' => 'required|numeric|min:0',
            'half_days' => 'required|integer|min:0',
            'paid_leaves' => 'required|numeric|min:0',
            'basic_salary' => 'required|numeric|min:0',
            'earned_salary' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'payment_status' => 'required|in:unpaid,paid',
            'payment_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        $validated['advance_deduction'] = (float) ($validated['advance_deduction'] ?? 0);

        // Check if salary slip already exists for this employee and month/year
        $existing = SalarySlip::where('employee_id', $validated['employee_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', 'Salary slip for this employee and month/year already exists (Slip #' . $existing->slip_number . ').');
        }

        // Generate Slip Number
        $monthStr = str_pad($validated['month'], 2, '0', STR_PAD_LEFT);
        $prefix = 'SLIP-' . $validated['year'] . $monthStr . '-';
        $lastSlip = SalarySlip::where('slip_number', 'like', $prefix . '%')->latest('id')->first();
        $seq = $lastSlip ? ((int) str_replace($prefix, '', $lastSlip->slip_number) + 1) : 1;
        $validated['slip_number'] = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $salarySlip = SalarySlip::create($validated);

            // Deduct advance if advance_deduction > 0
            if ($validated['advance_deduction'] > 0) {
                $remToDeduct = $validated['advance_deduction'];
                $activeAdvances = EmployeeAdvance::where('employee_id', $validated['employee_id'])
                    ->whereIn('status', ['pending', 'partially_deducted'])
                    ->orderBy('advance_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($activeAdvances as $adv) {
                    if ($remToDeduct <= 0) break;

                    $deductFromThis = min($remToDeduct, (float) $adv->remaining_amount);
                    if ($deductFromThis > 0) {
                        SalarySlipAdvanceDeduction::create([
                            'salary_slip_id' => $salarySlip->id,
                            'employee_advance_id' => $adv->id,
                            'amount' => $deductFromThis,
                        ]);

                        $adv->deducted_amount += $deductFromThis;
                        $adv->remaining_amount -= $deductFromThis;
                        $adv->status = $adv->remaining_amount <= 0 ? 'fully_deducted' : 'partially_deducted';
                        $adv->save();

                        $remToDeduct -= $deductFromThis;
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.salary-slips.show', $salarySlip->id)
                ->withSuccess('Salary slip created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error generating salary slip: ' . $e->getMessage());
        }
    }

    public function show(SalarySlip $salarySlip)
    {
        $salarySlip->load('employee', 'creator', 'advanceDeductions.employeeAdvance');
        return view('admin.salary_slips.show', compact('salarySlip'));
    }

    public function pdf(SalarySlip $salarySlip)
    {
        $salarySlip->load('employee', 'creator', 'advanceDeductions.employeeAdvance');
        return view('admin.salary_slips.pdf', compact('salarySlip'));
    }

    public function destroy(SalarySlip $salarySlip)
    {
        DB::beginTransaction();
        try {
            $advanceDeductions = SalarySlipAdvanceDeduction::where('salary_slip_id', $salarySlip->id)->get();
            foreach ($advanceDeductions as $deduction) {
                $adv = EmployeeAdvance::find($deduction->employee_advance_id);
                if ($adv) {
                    $adv->deducted_amount -= $deduction->amount;
                    $adv->remaining_amount += $deduction->amount;
                    $adv->status = $adv->deducted_amount <= 0 ? 'pending' : 'partially_deducted';
                    $adv->save();
                }
                $deduction->delete();
            }

            $salarySlip->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Salary slip deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete salary slip: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convert number to words format for net salary display
     */
    public static function numberToWords($number)
    {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy', '80' => 'Eighty',
            '90' => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
                $str [] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $hundred :
                    $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ?
            " and " . ($words[$point / 10] . " " . $words[$point % 10]) . " Paise" : '';
        return ($result ? $result . "Rupees " : "") . $points;
    }
}
