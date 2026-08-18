<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name'])]
class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    /**
     * Get the activities where this person is a PIC.
     */
    public function picActivities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_pic', 'person_id', 'activity_id');
    }

    /**
     * Get the activities where this person is a participant.
     */
    public function involvedActivities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_participant', 'person_id', 'activity_id');
    }

    /**
     * Get the milestones where this person is assigned.
     */
    public function milestones(): BelongsToMany
    {
        return $this->belongsToMany(Milestone::class, 'milestone_person', 'person_id', 'milestone_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the person profile details.
     */
    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PersonProfile::class);
    }

    /**
     * Get assigned targets checklist for this person.
     */
    public function assignedTargets(): HasMany
    {
        return $this->hasMany(PersonTarget::class);
    }

    /**
     * Get scale rating change histories for this person.
     */
    public function scaleHistories(): HasMany
    {
        return $this->hasMany(PersonScaleHistory::class)->with('user')->latest();
    }

    /**
     * Get multi-notes for this person.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(PersonNote::class)->with('user')->latest();
    }
}


