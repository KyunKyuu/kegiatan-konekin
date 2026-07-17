<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['category', 'description', 'activity_date', 'start_time', 'end_time', 'user_id'])]
class Activity extends Model
{
    use HasFactory;

    protected $casts = [
        'activity_date' => 'date',
    ];

    /**
     * Get the PICs for this activity.
     */
    public function pics(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'activity_pic', 'activity_id', 'person_id');
    }

    /**
     * Get the participants involved in this activity.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'activity_participant', 'activity_id', 'person_id');
    }

    /**
     * Get the user who created this activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
