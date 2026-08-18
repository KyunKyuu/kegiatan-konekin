<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonScaleHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'scale_type',
        'old_value',
        'new_value',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'old_value' => 'integer',
        'new_value' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
