<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');
        $type = $request->input('type');
        $search = $request->input('search');

        $query = Holiday::with('creator')->orderBy('from_date', 'asc');

        if ($year) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('from_date', $year)
                  ->orWhereYear('to_date', $year);
            });
        }

        if ($month) {
            $query->where(function ($q) use ($month) {
                $q->whereMonth('from_date', $month)
                  ->orWhereMonth('to_date', $month);
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', $escapedSearch)
                  ->orWhere('description', 'like', $escapedSearch);
            });
        }

        $holidays = $query->paginate(25)->withQueryString();

        // Statistics for current selected year
        $currentYear = $year ?: Carbon::now()->year;
        $totalHolidaysThisYear = Holiday::where(function ($q) use ($currentYear) {
            $q->whereYear('from_date', $currentYear)
              ->orWhereYear('to_date', $currentYear);
        })->sum('total_days');

        $today = Carbon::today()->format('Y-m-d');
        $upcomingHolidays = Holiday::where('to_date', '>=', $today)
            ->orderBy('from_date', 'asc')
            ->take(5)
            ->get();

        $publicCount = Holiday::where('type', 'public')
            ->where(function ($q) use ($currentYear) {
                $q->whereYear('from_date', $currentYear)->orWhereYear('to_date', $currentYear);
            })->count();

        $nationalCount = Holiday::where('type', 'national')
            ->where(function ($q) use ($currentYear) {
                $q->whereYear('from_date', $currentYear)->orWhereYear('to_date', $currentYear);
            })->count();

        $companyCount = Holiday::whereIn('type', ['company', 'optional'])
            ->where(function ($q) use ($currentYear) {
                $q->whereYear('from_date', $currentYear)->orWhereYear('to_date', $currentYear);
            })->count();

        return view('admin.holidays.index', compact(
            'holidays',
            'year',
            'month',
            'type',
            'search',
            'totalHolidaysThisYear',
            'upcomingHolidays',
            'publicCount',
            'nationalCount',
            'companyCount'
        ));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'type' => 'required|in:public,national,company,optional',
            'is_recurring_yearly' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = !empty($validated['to_date']) ? Carbon::parse($validated['to_date']) : $fromDate->copy();

        $totalDays = $fromDate->diffInDays($toDate) + 1;

        Holiday::create([
            'name' => $validated['name'],
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'total_days' => $totalDays,
            'type' => $validated['type'],
            'is_recurring_yearly' => $request->has('is_recurring_yearly') ? true : false,
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'type' => 'required|in:public,national,company,optional',
            'is_recurring_yearly' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = !empty($validated['to_date']) ? Carbon::parse($validated['to_date']) : $fromDate->copy();

        $totalDays = $fromDate->diffInDays($toDate) + 1;

        $holiday->update([
            'name' => $validated['name'],
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'total_days' => $totalDays,
            'type' => $validated['type'],
            'is_recurring_yearly' => $request->has('is_recurring_yearly') ? true : false,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Holiday deleted successfully.'
            ]);
        }

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }
}
