<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();

        $existingAttendances = Attendance::where('date', $date)
            ->get()
            ->keyBy('employee_id');

        $holiday = Holiday::getHolidayForDate($date);

        return view('admin.attendances.index', compact('date', 'employees', 'existingAttendances', 'holiday'));
    }

    public function saveBulk(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.status' => 'required|in:present,absent,half_day,leave,holiday',
            'attendances.*.check_in_time' => 'nullable',
            'attendances.*.check_out_time' => 'nullable',
            'attendances.*.remarks' => 'nullable|string',
        ]);

        $date = $request->input('date');
        $userId = auth()->id();

        foreach ($request->input('attendances') as $empId => $data) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $empId,
                    'date' => $date,
                ],
                [
                    'status' => $data['status'],
                    'check_in_time' => $data['check_in_time'] ?? null,
                    'check_out_time' => $data['check_out_time'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'created_by' => $userId,
                ]
            );
        }

        return redirect()->route('admin.attendances.index', ['date' => $date])
            ->withSuccess('Attendance records updated successfully.');
    }

    public function monthlyReport(Request $request)
    {
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $employees = Employee::orderBy('full_name')->get();

        $attendancesRaw = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $attendanceMap = [];
        foreach ($attendancesRaw as $att) {
            $dayNum = (int) Carbon::parse($att->date)->format('j');
            $attendanceMap[$att->employee_id][$dayNum] = $att->status;
        }

        $holidayDaysMap = Holiday::getHolidayDaysMapForMonth($year, $month);
        $holidays = Holiday::getHolidaysForMonth($year, $month);

        return view('admin.attendances.monthly_report', compact(
            'month',
            'year',
            'daysInMonth',
            'employees',
            'attendanceMap',
            'holidayDaysMap',
            'holidays',
            'startDate'
        ));
    }

    public function markHolidayFromMonthly(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,national,company,optional',
            'description' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->input('date'))->format('Y-m-d');
        $name = $request->input('name');
        $type = $request->input('type');
        $description = $request->input('description');
        $userId = auth()->id();

        // 1. Create or update Holiday
        $holiday = Holiday::where('from_date', '<=', $date)
            ->where('to_date', '>=', $date)
            ->first();

        if ($holiday) {
            $holiday->update([
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]);
        } else {
            Holiday::create([
                'name' => $name,
                'from_date' => $date,
                'to_date' => $date,
                'total_days' => 1,
                'type' => $type,
                'description' => $description,
                'created_by' => $userId,
            ]);
        }

        // 2. Mark attendance for all active employees as holiday
        $employees = Employee::where('is_active', true)->get();
        foreach ($employees as $emp) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'date' => $date,
                ],
                [
                    'status' => 'holiday',
                    'remarks' => $name . ' (' . ucfirst($type) . ' Holiday)',
                    'created_by' => $userId,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Holiday marked successfully and attendance updated for all employees.',
        ]);
    }

    public function unmarkHolidayFromMonthly(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->input('date'))->format('Y-m-d');

        // Remove holiday for that date
        Holiday::where('from_date', '<=', $date)
            ->where('to_date', '>=', $date)
            ->delete();

        // Remove holiday attendances for that date
        Attendance::where('date', $date)
            ->where('status', 'holiday')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Holiday unmarked and attendance cleared for ' . $date . '.',
        ]);
    }

    public function export(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();
        $existingAttendances = Attendance::where('date', $date)->get()->keyBy('employee_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Date: ' . $date);
        $sheet->setCellValue('A2', 'Employee Code');
        $sheet->setCellValue('B2', 'Employee Name');
        $sheet->setCellValue('C2', 'Status');
        $sheet->setCellValue('D2', 'Check In');
        $sheet->setCellValue('E2', 'Check Out');
        $sheet->setCellValue('F2', 'Remarks');

        $row = 3;
        foreach ($employees as $emp) {
            $att = $existingAttendances->get($emp->id);
            $sheet->setCellValue('A' . $row, $emp->employee_code);
            $sheet->setCellValue('B' . $row, $emp->full_name);
            $sheet->setCellValue('C' . $row, $att ? ucfirst($att->status) : 'Not Marked');
            $sheet->setCellValue('D' . $row, $att ? $att->check_in_time : '-');
            $sheet->setCellValue('E' . $row, $att ? $att->check_out_time : '-');
            $sheet->setCellValue('F' . $row, $att ? $att->remarks : '');
            $row++;
        }

        $writer = new Xls($spreadsheet);
        $path = storage_path('app/attendance_export.xls');
        $writer->save($path);

        return response()->download($path, 'attendance_' . $date . '.xls')->deleteFileAfterSend(true);
    }
}
