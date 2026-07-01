<?php

namespace App\Models;

use App\Support\DurationFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Detainee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'charge_description',
        'charge_rpc_code',
        'commitment_date',
        'facility_id',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'commitment_date' => 'date',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function penaltyReference(): BelongsTo
    {
        return $this->belongsTo(PenaltyReference::class, 'charge_rpc_code');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(DetaineePhase::class);
    }

    public function overstayComputations(): HasMany
    {
        return $this->hasMany(OverstayComputation::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function legalActions(): HasMany
    {
        return $this->hasMany(LegalAction::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get days detained from commitment date to now.
     */
    public function getDaysDetainedAttribute(): int
    {
        return $this->commitment_date->diffInDays(Carbon::today());
    }

    public function getDaysDetainedDisplayAttribute(): string
    {
        return DurationFormatter::daysWithParenthetical($this->days_detained);
    }

    /**
     * Get the latest alert level for this detainee.
     */
    public function getLatestAlertAttribute(): ?Alert
    {
        return $this->alerts()->latest()->first();
    }
}
