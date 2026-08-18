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
     * Display the full-page ruler timeline for capstone objectives.
     */
    public function index(Request $request)
    {
        $mainMilestones = Milestone::with(['subMilestones.people', 'people', 'creator'])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        $allPeople = Person::orderBy('name')->get();
        $masterTargets = MasterTarget::orderBy('title')->get();

        return view('monitoring.index', compact(
            'mainMilestones',
            'allPeople',
            'masterTargets'
        ));
    }

    /**
     * Display the full-page person detail page.
     */
    public function showPersonDetail($id)
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

        // Filter out activities where this person is the PIC (User instruction: "yang jadi PIC di acara, jangan ada di daftar")
        $nonPicActivities = $person->involvedActivities
            ->reject(function ($act) use ($person) {
                return $act->pic_person_id == $person->id;
            })
            ->unique('id')
            ->values();

        $masterTargets = MasterTarget::orderBy('title')->get();

        $allMilestones = Milestone::with('subMilestones')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        return view('monitoring.show_person', compact(
            'person',
            'nonPicActivities',
            'masterTargets',
            'allMilestones'
        ));
    }

    /**
     * Move a person to a new Milestone / Sub-Milestone step.
     */
    public function movePersonMilestone(Request $request, $id)
    {
        $person = Person::findOrFail($id);

        $validated = $request->validate([
            'milestone_id' => 'nullable|exists:milestones,id',
        ]);

        if (!empty($validated['milestone_id'])) {
            $person->milestones()->sync([$validated['milestone_id']]);
        } else {
            $person->milestones()->detach();
        }

        return redirect()->back()->with('success', 'Posisi Milestone/Step ' . $person->name . ' berhasil diperbarui!');
    }

    /**
     * Store a new capstone or sub capstone objective.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
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
     * Update an existing milestone objective.
     */
    public function update(Request $request, $id)
    {
        $milestone = Milestone::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
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
            'color' => $validated['color'],
        ]);

        $personIds = $this->resolvePersonIds($request->input('people', []), $request->input('new_people'));
        $milestone->people()->sync($personIds);

        return redirect()->route('monitoring.index')->with('success', 'Capstone berhasil diperbarui!');
    }

    /**
     * Remove the specified milestone objective.
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

        // Filter out activities where person is PIC
        $nonPicActivities = $person->involvedActivities
            ->reject(function ($act) use ($person) {
                return $act->pic_person_id == $person->id;
            })
            ->unique('id')
            ->values();

        $masterTargets = MasterTarget::orderBy('title')->get();

        return response()->json([
            'person' => $person,
            'profile' => $person->profile,
            'milestones' => $person->milestones,
            'calendar_activities' => $nonPicActivities,
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

        return redirect()->back()->with('success', 'Profil dan histori skala ' . $person->name . ' berhasil diperbarui!');
    }

    /**
     * Toggle completion status of an assigned person target.
     * Once checked/completed, it is LOCKED (non-fillable / non-toggleable).
     */
    public function togglePersonTarget($targetId)
    {
        $target = PersonTarget::findOrFail($targetId);
        
        if ($target->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Target yang sudah di-ceklis telah terkunci (non-fillable).',
                'is_completed' => true,
                'completed_at' => $target->completed_at ? $target->completed_at->format('d M Y H:i') : null,
            ]);
        }

        $target->is_completed = true;
        $target->completed_at = now();
        $target->save();

        return response()->json([
            'success' => true,
            'is_completed' => true,
            'completed_at' => $target->completed_at->format('d M Y H:i'),
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
            'is_completed' => false,
            'title' => $title,
        ]);

        return redirect()->back()->with('success', 'Target berhasil ditambahkan ke ' . $person->name);
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

        return redirect()->back()->with('success', 'Master Target baru berhasil dibuat!');
    }

    /**
     * Delete a Master Target globally (Admin only).
     */
    public function destroyMasterTarget($id)
    {
        $master = MasterTarget::findOrFail($id);
        $master->delete();

        return redirect()->back()->with('success', 'Master Target berhasil dihapus!');
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

        return redirect()->back()->with('success', 'Catatan baru berhasil ditambahkan!');
    }

    /**
     * Delete a note from a person.
     */
    public function deletePersonNote($id)
    {
        $note = PersonNote::findOrFail($id);
        $note->delete();

        return redirect()->back()->with('success', 'Catatan berhasil dihapus!');
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
