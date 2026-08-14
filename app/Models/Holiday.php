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
        return self::getHolidayForDate($date) !== null;
    }

    /**
     * Get the holiday model for a given date if any.
     */
    public static function getHolidayForDate($date)
    {
        $d = Carbon::parse($date);
        $dateStr = $d->format('Y-m-d');
        $month = (int) $d->format('m');
        $day = (int) $d->format('d');

        // Direct date match first
        $holiday = self::where('from_date', '<=', $dateStr)
            ->where('to_date', '>=', $dateStr)
            ->first();

        if ($holiday) {
            return $holiday;
        }

        // Recurring yearly match
        return self::where('is_recurring_yearly', true)
            ->whereMonth('from_date', '<=', $month)
            ->whereMonth('to_date', '>=', $month)
            ->get()
            ->first(function ($h) use ($d) {
                $from = Carbon::parse($h->from_date)->year($d->year);
                $to = Carbon::parse($h->to_date)->year($d->year);
                return $d->betweenIncluded($from, $to);
            });
    }

    /**
     * Get all holidays occurring in a given month and year.
     */
    public static function getHolidaysForMonth(int $year, int $month)
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        return self::where(function ($query) use ($startOfMonth, $endOfMonth, $month) {
            $query->whereBetween('from_date', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('to_date', [$startOfMonth, $endOfMonth])
                ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('from_date', '<=', $startOfMonth)
                      ->where('to_date', '>=', $endOfMonth);
                })
                ->orWhere(function ($q) use ($month) {
                    $q->where('is_recurring_yearly', true)
                      ->where(function ($rq) use ($month) {
                          $rq->whereMonth('from_date', $month)
                             ->orWhereMonth('to_date', $month);
                      });
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

            if ($holiday->is_recurring_yearly) {
                $from = $from->copy()->year($year);
                $to = $to->copy()->year($year);
            }

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDayDate = Carbon::createFromDate($year, $month, $day);
                if ($currentDayDate->betweenIncluded($from, $to)) {
                    $holidayDaysMap[$day] = $holiday;
                }
            }
        }

        return $holidayDaysMap;
    }

    /**
     * Seed default standard holidays for a given year if requested.
     */
    public static function seedDefaultHolidaysForYear(int $year, $userId = null)
    {
        $defaults = [
            ['name' => 'New Year\'s Day', 'date' => "$year-01-01", 'type' => 'public', 'recurring' => true],
            ['name' => 'Republic Day', 'date' => "$year-01-26", 'type' => 'national', 'recurring' => true],
            ['name' => 'Labor Day / May Day', 'date' => "$year-05-01", 'type' => 'public', 'recurring' => true],
            ['name' => 'Independence Day', 'date' => "$year-08-15", 'type' => 'national', 'recurring' => true],
            ['name' => 'Mahatma Gandhi Jayanti', 'date' => "$year-10-02", 'type' => 'national', 'recurring' => true],
            ['name' => 'Christmas Day', 'date' => "$year-12-25", 'type' => 'public', 'recurring' => true],
        ];

        foreach ($defaults as $def) {
            $exists = self::where('from_date', $def['date'])
                ->orWhere(function ($q) use ($def) {
                    $q->where('name', $def['name'])->where('is_recurring_yearly', true);
                })
                ->exists();

            if (!$exists) {
                self::create([
                    'name' => $def['name'],
                    'from_date' => $def['date'],
                    'to_date' => $def['date'],
                    'total_days' => 1,
                    'type' => $def['type'],
                    'is_recurring_yearly' => $def['recurring'],
                    'created_by' => $userId,
                ]);
            }
        }
    }
}
