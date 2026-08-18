<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Person;
use App\Models\MasterTarget;
use App\Models\PersonTarget;
use App\Models\PersonScaleHistory;
use App\Models\PersonNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    /**
     * Display the ruler timeline and capstone monitoring dashboard.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status');

        $query = Milestone::with(['subMilestones.people', 'people', 'creator'])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('target_date', 'asc');

        if ($statusFilter && in_array($statusFilter, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $statusFilter);
        }

        $mainMilestones = $query->get();
        $allPeople = Person::orderBy('name')->get();
        $masterTargets = MasterTarget::orderBy('title')->get();

        // Calculate stats
        $totalMain = Milestone::whereNull('parent_id')->count();
        $totalSub = Milestone::whereNotNull('parent_id')->count();
        $completedMain = Milestone::whereNull('parent_id')->where('status', 'completed')->count();
        $completedSub = Milestone::whereNotNull('parent_id')->where('status', 'completed')->count();
        
        $totalCount = $totalMain + $totalSub;
        $completedCount = $completedMain + $completedSub;
        $progressPercent = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        return view('monitoring.index', compact(
            'mainMilestones',
            'allPeople',
            'masterTargets',
            'totalMain',
            'totalSub',
            'completedCount',
            'progressPercent',
            'statusFilter'
        ));
    }

    /**
     * Store a new capstone or sub capstone.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
            'color' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:milestones,id',
            'people' => 'nullable|array',
            'new_people' => 'nullable|string',
        ]);

        $maxOrder = Milestone::where('parent_id', $validated['parent_id'] ?? null)->max('order') ?? 0;

        $milestone = Milestone::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status' => $validated['status'],
            'color' => $validated['color'],
            'order' => $maxOrder + 1,
            'created_by' => Auth::id(),
        ]);

        $personIds = $this->resolvePersonIds($request->input('people', []), $request->input('new_people'));
        if (!empty($personIds)) {
            $milestone->people()->sync($personIds);
        }

        return redirect()->route('monitoring.index')->with('success', 'Capstone berhasil ditambahkan!');
    }

    /**
     * Update an existing milestone.
     */
    public function update(Request $request, $id)
    {
        $milestone = Milestone::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
            'color' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:milestones,id',
            'people' => 'nullable|array',
            'new_people' => 'nullable|string',
        ]);

        if (isset($validated['parent_id']) && $validated['parent_id'] == $milestone->id) {
            $validated['parent_id'] = null;
        }

        $milestone->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status' => $validated['status'],
            'color' => $validated['color'],
        ]);

        $personIds = $this->resolvePersonIds($request->input('people', []), $request->input('new_people'));
        $milestone->people()->sync($personIds);

        return redirect()->route('monitoring.index')->with('success', 'Capstone berhasil diperbarui!');
    }

    /**
     * Remove the specified milestone.
     */
    public function destroy($id)
    {
        $milestone = Milestone::findOrFail($id);
        $milestone->delete();

        return redirect()->route('monitoring.index')->with('success', 'Capstone berhasil dihapus!');
    }

    /**
     * Add/sync people to a milestone directly.
     */
    public function assignPeople(Request $request, $id)
    {
        $milestone = Milestone::findOrFail($id);
        
        $personIds = $this->resolvePersonIds($request->input('people', []), $request->input('new_people'));
        $milestone->people()->syncWithoutDetaching($personIds);

        return redirect()->route('monitoring.index')->with('success', 'Penanggung jawab berhasil ditambahkan!');
    }

    /**
     * Get person detail JSON including profile, milestones, calendar activities, assigned targets, scale histories, and multi-notes.
     */
    public function getPersonDetail($id)
    {
        $person = Person::with([
            'profile',
            'milestones',
            'picActivities',
            'involvedActivities',
            'assignedTargets',
            'scaleHistories.user',
            'notes.user'
        ])->findOrFail($id);

        if (!$person->profile) {
            $person->profile()->create([
                'catatan_akademik' => 'Aktif',
                'catatan_keuangan' => 'Lunas',
                'skala_sales' => 3,
                'skala_katim' => 3,
                'skala_keaktifan' => 4,
                'skala_prioritas' => 3,
                'cara_aktif' => 'Selalu hadir tepat waktu dan aktif dalam diskusi tim.',
            ]);
            $person->load('profile');
        }

        // Auto seed default assigned targets if empty
        if ($person->assignedTargets->isEmpty()) {
            PersonTarget::create([
                'person_id' => $person->id,
                'title' => '++PZ',
                'is_completed' => true,
                'completed_at' => now(),
            ]);
            PersonTarget::create([
                'person_id' => $person->id,
                'title' => '++01',
                'is_completed' => false,
            ]);
            $person->load('assignedTargets');
        }

        // Auto seed initial notes if empty
        if ($person->notes->isEmpty()) {
            PersonNote::create([
                'person_id' => $person->id,
                'category' => 'Akademik',
                'status_label' => 'Aktif',
                'note' => 'Mahasiswa aktif semester ini.',
                'created_by' => Auth::id(),
            ]);
            PersonNote::create([
                'person_id' => $person->id,
                'category' => 'Keuangan',
                'status_label' => 'Lunas',
                'note' => 'Pembayaran SPP dan administrasi lunas.',
                'created_by' => Auth::id(),
            ]);
            $person->load('notes');
        }

        // Merge activities from calendar
        $calendarActivities = $person->picActivities->merge($person->involvedActivities)->unique('id')->values();
        $masterTargets = MasterTarget::orderBy('title')->get();

        return response()->json([
            'person' => $person,
            'profile' => $person->profile,
            'milestones' => $person->milestones,
            'calendar_activities' => $calendarActivities,
            'assigned_targets' => $person->assignedTargets,
            'scale_histories' => $person->scaleHistories,
            'notes' => $person->notes,
            'master_targets' => $masterTargets,
        ]);
    }

    /**
     * Update person profile details and record scale change history with reasons.
     */
    public function updatePersonProfile(Request $request, $id)
    {
        $person = Person::findOrFail($id);

        $validated = $request->validate([
            'skala_sales' => 'required|integer|min:1|max:5',
            'skala_katim' => 'required|integer|min:1|max:5',
            'skala_keaktifan' => 'required|integer|min:1|max:5',
            'skala_prioritas' => 'required|integer|min:1|max:5',
            'cara_aktif' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $oldProfile = $person->profile;
        $reason = $validated['reason'] ?? 'Pembaruan penilaian berkala oleh admin.';

        $scalesToCheck = [
            'sales' => ['old' => $oldProfile->skala_sales ?? 3, 'new' => $validated['skala_sales']],
            'katim' => ['old' => $oldProfile->skala_katim ?? 3, 'new' => $validated['skala_katim']],
            'keaktifan' => ['old' => $oldProfile->skala_keaktifan ?? 4, 'new' => $validated['skala_keaktifan']],
            'prioritas' => ['old' => $oldProfile->skala_prioritas ?? 3, 'new' => $validated['skala_prioritas']],
        ];

        foreach ($scalesToCheck as $scaleKey => $vals) {
            if ($vals['old'] != $vals['new']) {
                PersonScaleHistory::create([
                    'person_id' => $person->id,
                    'scale_type' => $scaleKey,
                    'old_value' => $vals['old'],
                    'new_value' => $vals['new'],
                    'reason' => $reason,
                    'changed_by' => Auth::id(),
                ]);
            }
        }

        $person->profile()->updateOrCreate(
            ['person_id' => $person->id],
            [
                'skala_sales' => $validated['skala_sales'],
                'skala_katim' => $validated['skala_katim'],
                'skala_keaktifan' => $validated['skala_keaktifan'],
                'skala_prioritas' => $validated['skala_prioritas'],
                'cara_aktif' => $validated['cara_aktif'] ?? null,
            ]
        );

        return redirect()->route('monitoring.index')->with('success', 'Profil dan histori skala ' . $person->name . ' berhasil diperbarui!');
    }

    /**
     * Toggle completion status of an assigned person target.
     */
    public function togglePersonTarget($targetId)
    {
        $target = PersonTarget::findOrFail($targetId);
        $target->is_completed = !$target->is_completed;
        $target->completed_at = $target->is_completed ? now() : null;
        $target->save();

        return response()->json([
            'success' => true,
            'is_completed' => $target->is_completed,
            'completed_at' => $target->completed_at ? $target->completed_at->format('d M Y H:i') : null,
        ]);
    }

    /**
     * Assign target from Master Target or custom target to a person.
     */
    public function assignMasterTarget(Request $request, $personId)
    {
        $person = Person::findOrFail($personId);

        $validated = $request->validate([
            'master_target_id' => 'nullable|exists:master_targets,id',
            'custom_title' => 'nullable|string|max:255',
        ]);

        $title = $validated['custom_title'] ?? null;
        $masterId = $validated['master_target_id'] ?? null;

        if ($masterId && empty($title)) {
            $master = MasterTarget::find($masterId);
            $title = $master ? $master->title : 'Target Baru';
        }

        if (empty($title)) {
            return redirect()->back()->withErrors(['title' => 'Judul target harus diisi.']);
        }

        PersonTarget::create([
            'person_id' => $person->id,
            'master_target_id' => $masterId,
            'title' => $title,
            'is_completed' => false,
        ]);

        return redirect()->route('monitoring.index')->with('success', 'Target berhasil ditambahkan ke ' . $person->name);
    }

    /**
     * Store new Master Target globally (Admin only).
     */
    public function storeMasterTarget(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MasterTarget::create($validated);

        return redirect()->route('monitoring.index')->with('success', 'Master Target baru berhasil dibuat!');
    }

    /**
     * Delete a Master Target globally (Admin only).
     */
    public function destroyMasterTarget($id)
    {
        $master = MasterTarget::findOrFail($id);
        $master->delete();

        return redirect()->route('monitoring.index')->with('success', 'Master Target berhasil dihapus!');
    }

    /**
     * Add a new note (Multi Catatan) to a person.
     */
    public function addPersonNote(Request $request, $personId)
    {
        $person = Person::findOrFail($personId);

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'status_label' => 'nullable|string|max:100',
            'note' => 'nullable|string',
        ]);

        PersonNote::create([
            'person_id' => $person->id,
            'category' => $validated['category'],
            'status_label' => $validated['status_label'] ?? null,
            'note' => $validated['note'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('monitoring.index')->with('success', 'Catatan baru berhasil ditambahkan!');
    }

    /**
     * Delete a note from a person.
     */
    public function deletePersonNote($id)
    {
        $note = PersonNote::findOrFail($id);
        $note->delete();

        return redirect()->route('monitoring.index')->with('success', 'Catatan berhasil dihapus!');
    }

    /**
     * Helper to process existing person IDs and create new Person records for comma-separated names.
     */
    private function resolvePersonIds($existingIds = [], $newPeopleString = null): array
    {
        $personIds = is_array($existingIds) ? array_map('intval', $existingIds) : [];

        if (!empty($newPeopleString)) {
            $names = array_filter(array_map('trim', explode(',', $newPeopleString)));
            foreach ($names as $name) {
                if (strlen($name) > 0) {
                    $person = Person::firstOrCreate(['name' => $name]);
                    if (!in_array($person->id, $personIds)) {
                        $personIds[] = $person->id;
                    }
                }
            }
        }

        return $personIds;
    }
}
