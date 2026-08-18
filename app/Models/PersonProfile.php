<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'targets',
        'catatan_akademik',
        'catatan_keuangan',
        'skala_sales',
        'skala_katim',
        'skala_keaktifan',
        'skala_prioritas',
        'cara_aktif',
    ];

    protected $casts = [
        'targets' => 'array',
        'skala_sales' => 'integer',
        'skala_katim' => 'integer',
        'skala_keaktifan' => 'integer',
        'skala_prioritas' => 'integer',
    ];

    /**
     * Get the person that owns this profile.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
