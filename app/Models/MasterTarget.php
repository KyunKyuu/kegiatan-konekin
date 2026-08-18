<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function personTargets(): HasMany
    {
        return $this->hasMany(PersonTarget::class);
    }
}
