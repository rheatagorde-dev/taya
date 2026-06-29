<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class OverstayComputation extends Model
{
    use HasFactory;

    protected $fillable = [
        'detainee_id',
        'days_detained',
        'max_penalty_days',
        'overstay_days',
        'alert_level',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'computed_at' => 'datetime',
        ];
    }

    public function detainee(): BelongsTo
    {
        return $this->belongsTo(Detainee::class);
    }

    public function alert(): HasOne
    {
        return $this->hasOne(Alert::class, 'computation_id');
    }
}
