<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from_date',
        'to_date',
        'total_days',
        'type',
        'is_recurring_yearly',
        'description',
        'created_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_days' => 'integer',
        'is_recurring_yearly' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a given date falls on any holiday.
     */
    public static function isHoliday($date): bool
    {
        $d = Carbon::parse($date)->format('Y-m-d');
        return self::where('from_date', '<=', $d)
            ->where('to_date', '>=', $d)
            ->exists();
    }

    /**
     * Get the holiday model for a given date if any.
     */
    public static function getHolidayForDate($date)
    {
        $d = Carbon::parse($date)->format('Y-m-d');
        return self::where('from_date', '<=', $d)
            ->where('to_date', '>=', $d)
            ->first();
    }

    /**
     * Get all holidays occurring in a given month and year.
     */
    public static function getHolidaysForMonth(int $year, int $month)
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        return self::where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('from_date', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('to_date', [$startOfMonth, $endOfMonth])
                ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('from_date', '<=', $startOfMonth)
                      ->where('to_date', '>=', $endOfMonth);
                });
        })->orderBy('from_date')->get();
    }

    /**
     * Get a map of day number -> Holiday instance for a month.
     */
    public static function getHolidayDaysMapForMonth(int $year, int $month): array
    {
        $holidays = self::getHolidaysForMonth($year, $month);
        $holidayDaysMap = [];

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        foreach ($holidays as $holiday) {
            $from = Carbon::parse($holiday->from_date);
            $to = Carbon::parse($holiday->to_date);

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDayDate = Carbon::createFromDate($year, $month, $day);
                if ($currentDayDate->betweenIncluded($from, $to)) {
                    $holidayDaysMap[$day] = $holiday;
                }
            }
        }

        return $holidayDaysMap;
    }
}
