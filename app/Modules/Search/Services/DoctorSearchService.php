<?php

namespace App\Modules\Search\Services;

use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorSearchService
{
    /**
     * Validate the GET filters (same rules the old DoctorFilterRequest
     * carried; kept here so core never type-hints a module class).
     */
    public function validatedFilters(Request $request): array
    {
        return Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
        ])->validate();
    }

    /**
     * Active doctors matching the filters. Text search hits name,
     * specialist and department; the date filter keeps only doctors
     * with open slots that day (schedule + off-day aware).
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Doctor::with(['schedules.timeSlot'])->where('status', 1);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('specialist', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $department = trim((string) ($filters['department'] ?? ''));
        if ($department !== '') {
            $query->where('department', $department);
        }

        $date = $filters['date'] ?? null;
        if (is_string($date) && $date !== '') {
            $weekday = Carbon::parse($date)->dayOfWeek;
            $query->where(function ($q) use ($weekday) {
                $q->whereHas('schedules', function ($s) use ($weekday) {
                    $s->where('weekday', $weekday)
                        ->whereHas('timeSlot', fn ($t) => $t->where('status', 1));
                })->orWhereDoesntHave('schedules');
            })->whereDoesntHave('offDays', fn ($o) => $o->whereDate('date', $date));
        }

        return $query->latest()->paginate(12)->withQueryString();
    }

    /**
     * Distinct department names for the filter dropdown (single query).
     */
    public function departments(): array
    {
        return Doctor::where('status', 1)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->all();
    }
}
