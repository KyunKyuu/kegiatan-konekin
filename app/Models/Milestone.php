<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'target_date',
        'status',
        'color',
        'order',
        'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'order' => 'integer',
    ];

    /**
     * Get the parent milestone (if this is a sub capstone).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'parent_id');
    }

    /**
     * Get sub milestones (sub capstones).
     */
    public function subMilestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'parent_id')->orderBy('order')->orderBy('target_date');
    }

    /**
     * Get people assigned to this milestone.
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'milestone_person', 'milestone_id', 'person_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the user who created this milestone.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
