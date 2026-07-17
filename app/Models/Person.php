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
}
