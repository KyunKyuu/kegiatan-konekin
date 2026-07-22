<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Person;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->input('view', 'month');
        $dateParam = $request->input('date');

        if ($dateParam) {
            try {
                $activeDate = Carbon::parse($dateParam);
            } catch (\Exception $e) {
                $activeDate = Carbon::today();
            }
        } else {
            $activeDate = Carbon::today();
        }

        // If month or year is explicitly requested (e.g. from the select dropdowns)
        if ($request->has('month') || $request->has('year')) {
            $month = (int) $request->input('month', $activeDate->month);
            $year = (int) $request->input('year', $activeDate->year);
            // Handle bounds for month
            if ($month < 1) {
                $month = 12;
                $year--;
            } elseif ($month > 12) {
                $month = 1;
                $year++;
            }
            $activeDate = Carbon::create($year, $month, 1);
        } else {
            $month = $activeDate->month;
            $year = $activeDate->year;
        }

        $date = Carbon::create($year, $month, 1);
        
        // Calendar grid starts at the start of the week of the first day of the month
        // Let's use Sunday as the first day of the week, standard for calendar views
        $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Fetch activities in this range
        $activitiesQuery = Activity::with(['pics', 'participants', 'creator'])
            ->whereBetween('activity_date', [
                $startOfCalendar->format('Y-m-d'),
                $endOfCalendar->format('Y-m-d')
            ]);

        // Apply filters
        $search = $request->input('search');
        if ($search) {
            $activitiesQuery->where('description', 'like', '%' . $search . '%');
        }

        $selectedCategories = $request->input('categories', []);
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            $activitiesQuery->whereIn('category', $selectedCategories);
        }

        $personId = $request->input('person_id');
        $selectedPerson = $personId ? Person::find($personId) : null;
        if ($selectedPerson) {
            $this->applyPersonFilter($activitiesQuery, $selectedPerson->id);
        }

        // Order by start_time (all-day activities with a null start_time come
        // first) instead of insertion order, so the month grid badges and the
        // Agenda/list view both read chronologically.
        $activities = $activitiesQuery->orderBy('start_time')->get()->groupBy(function($activity) {
            return $activity->activity_date->format('Y-m-d');
        });

        // Get categories from database dynamically
        $categories = Category::all();
        $allCategories = $categories->pluck('name')->toArray();
        $categoryColors = $categories->pluck('color', 'name')->toArray();

        // Build grid of days
        $days = [];
        $tempDate = $startOfCalendar->copy();
        while ($tempDate->lte($endOfCalendar)) {
            $formattedDate = $tempDate->format('Y-m-d');
            $days[] = [
                'date' => $tempDate->copy(),
                'formatted' => $formattedDate,
                'is_current_month' => $tempDate->month === $month,
                'is_today' => $tempDate->isToday(),
                'activities' => $activities->get($formattedDate, collect()),
            ];
            $tempDate->addDay();
        }

        // Fetch daily activities for Day view
        $activeDateStr = $activeDate->format('Y-m-d');
        $dayActivitiesQuery = Activity::with(['pics', 'participants', 'creator'])
            ->whereDate('activity_date', $activeDateStr);
            
        if ($search) {
            $dayActivitiesQuery->where('description', 'like', '%' . $search . '%');
        }
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            $dayActivitiesQuery->whereIn('category', $selectedCategories);
        }
        if ($selectedPerson) {
            $this->applyPersonFilter($dayActivitiesQuery, $selectedPerson->id);
        }

        $dayActivities = $dayActivitiesQuery->get();
        $allDayActivities = $dayActivities->filter(fn($a) => is_null($a->start_time))->values();
        $timedActivities = $dayActivities->filter(fn($a) => !is_null($a->start_time))->sortBy('start_time')->values();

        // Overnight activities (end_time <= start_time, e.g. 20:00 -> 08:00) that
        // started yesterday and roll into today's view.
        $prevDateStr = $activeDate->copy()->subDay()->format('Y-m-d');
        $overnightQuery = Activity::with(['pics', 'participants', 'creator'])
            ->whereDate('activity_date', $prevDateStr)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->whereColumn('end_time', '<=', 'start_time');

        if ($search) {
            $overnightQuery->where('description', 'like', '%' . $search . '%');
        }
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            $overnightQuery->whereIn('category', $selectedCategories);
        }
        if ($selectedPerson) {
            $this->applyPersonFilter($overnightQuery, $selectedPerson->id);
        }

        $overnightFromYesterday = $overnightQuery->get();
        foreach ($overnightFromYesterday as $activity) {
            $activity->continues_from_previous_day = true;
        }

        $timedActivities = $timedActivities->concat($overnightFromYesterday)->sortBy('start_time')->values();

        return view('calendar', [
            'year' => $year,
            'month' => $month,
            'monthName' => $date->translatedFormat('F'),
            'days' => $days,
            'allCategories' => $allCategories,
            'categoryColors' => $categoryColors,
            'selectedCategories' => $selectedCategories,
            'search' => $search,
            'personId' => $personId,
            'selectedPerson' => $selectedPerson,
            'view' => $view,
            'activeDate' => $activeDate,
            'activeDateStr' => $activeDateStr,
            'allDayActivities' => $allDayActivities,
            'timedActivities' => $timedActivities,
        ]);
    }

    /**
     * Restrict a query to activities where the given person is either a PIC
     * or a participant.
     */
    private function applyPersonFilter($query, int $personId): void
    {
        $query->where(function ($q) use ($personId) {
            $q->whereHas('pics', fn($q2) => $q2->where('people.id', $personId))
              ->orWhereHas('participants', fn($q2) => $q2->where('people.id', $personId));
        });
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu untuk menambah kegiatan.'], 401);
            }
            return redirect()->route('login')->withErrors(['message' => 'Silakan login terlebih dahulu untuk menambah kegiatan.']);
        }

        $request->validate([
            'category' => 'required|string|max:255',
            'activity_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|required_with:start_time|date_format:H:i',
            'description' => 'required|string',
            'pics' => 'required|array|min:1',
            'pics.*' => 'required|string|max:255',
            'participants' => 'nullable|array',
            'participants.*' => 'required|string|max:255',
        ], $this->activityValidationMessages());

        try {
            DB::beginTransaction();

            // 1. Create the activity
            $activity = Activity::create([
                'category' => $request->category,
                'activity_date' => $request->activity_date,
                'start_time' => $request->start_time ?: null,
                'end_time' => $request->end_time ?: null,
                'description' => $request->description,
                'user_id' => Auth::id(),
            ]);

            // 2. Resolve and sync PICs
            $picNames = $request->input('pics', []);
            $picIds = [];

            foreach ($picNames as $name) {
                $trimmedName = trim($name);
                if (empty($trimmedName)) continue;

                $pic = Person::firstOrCreate(['name' => $trimmedName]);
                $picIds[] = $pic->id;
            }
            $activity->pics()->sync($picIds);

            // 3. Resolve and sync Participants
            $participantNames = $request->input('participants', []);
            $participantIds = [];

            foreach ($participantNames as $name) {
                $trimmedName = trim($name);
                if (empty($trimmedName)) continue;

                $participant = Person::firstOrCreate(['name' => $trimmedName]);
                $participantIds[] = $participant->id;
            }

            $activity->participants()->sync($participantIds);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Kegiatan berhasil ditambahkan!']);
            }
            return redirect()->back()->with('success', 'Kegiatan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menambahkan kegiatan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Gagal menambahkan kegiatan: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }
            return redirect()->route('login');
        }

        $activity = Activity::findOrFail($id);

        // Allow only creator or admin to edit
        if (Auth::id() !== $activity->user_id && !Auth::user()->is_admin) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengubah kegiatan ini.'], 403);
            }
            return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki akses untuk mengubah kegiatan ini.']);
        }

        $request->validate([
            'category' => 'required|string|max:255',
            'activity_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|required_with:start_time|date_format:H:i',
            'description' => 'required|string',
            'pics' => 'required|array|min:1',
            'pics.*' => 'required|string|max:255',
            'participants' => 'nullable|array',
            'participants.*' => 'required|string|max:255',
        ], $this->activityValidationMessages());

        try {
            DB::beginTransaction();

            // 1. Update the activity
            $activity->update([
                'category' => $request->category,
                'activity_date' => $request->activity_date,
                'start_time' => $request->start_time ?: null,
                'end_time' => $request->end_time ?: null,
                'description' => $request->description,
            ]);

            // 2. Resolve and sync PICs
            $picNames = $request->input('pics', []);
            $picIds = [];

            foreach ($picNames as $name) {
                $trimmedName = trim($name);
                if (empty($trimmedName)) continue;

                $pic = Person::firstOrCreate(['name' => $trimmedName]);
                $picIds[] = $pic->id;
            }
            $activity->pics()->sync($picIds);

            // 3. Resolve and sync Participants
            $participantNames = $request->input('participants', []);
            $participantIds = [];

            foreach ($participantNames as $name) {
                $trimmedName = trim($name);
                if (empty($trimmedName)) continue;

                $participant = Person::firstOrCreate(['name' => $trimmedName]);
                $participantIds[] = $participant->id;
            }

            $activity->participants()->sync($participantIds);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Kegiatan berhasil diperbarui!']);
            }
            return redirect()->back()->with('success', 'Kegiatan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal memperbarui kegiatan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui kegiatan: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }
            return redirect()->route('login');
        }

        $activity = Activity::findOrFail($id);

        // Allow only creator or admin to delete
        if (Auth::id() !== $activity->user_id && !Auth::user()->is_admin) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus kegiatan ini.'], 403);
            }
            return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki akses untuk menghapus kegiatan ini.']);
        }

        $activity->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Kegiatan berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus!');
    }

    /**
     * Custom Indonesian validation messages for the store/update rules.
     *
     * The app has no lang/ files published (APP_LOCALE and APP_FALLBACK_LOCALE
     * are both "id" with no translations available), so unmapped rules render
     * as raw keys like "validation.after" instead of a readable message.
     */
    private function activityValidationMessages(): array
    {
        return [
            'category.required' => 'Kategori kegiatan wajib dipilih.',
            'activity_date.required' => 'Tanggal kegiatan wajib diisi.',
            'activity_date.date' => 'Tanggal kegiatan tidak valid.',
            'start_time.date_format' => 'Format jam mulai tidak valid.',
            'end_time.required_with' => 'Jam selesai wajib diisi jika jam mulai diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid.',
            'description.required' => 'Isi kegiatan wajib diisi.',
            'pics.required' => 'Minimal harus ada 1 PIC kegiatan.',
            'pics.min' => 'Minimal harus ada 1 PIC kegiatan.',
            'pics.*.required' => 'Nama PIC tidak boleh kosong.',
            'participants.*.required' => 'Nama peserta tidak boleh kosong.',
        ];
    }
}
