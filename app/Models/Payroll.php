<?php
// app/Models/Payroll.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payroll extends Model
{
    protected $fillable = [
        'year',
        'month',
        'period_start',
        'period_end',
        'status',
        'processed_at',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'processed_at' => 'datetime',
    ];

    // Relations
    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'processed_by');
    }

    // Helpers

    /**
     * Label cantik buat dropdown / table
     * Contoh: Januari 2026 (01 Jan - 13 Jan)
     */
    public function getLabelAttribute(): string
    {
        return sprintf(
            '%s %s (%s - %s)',
            Carbon::create()->month($this->month)->translatedFormat('F'),
            $this->year,
            $this->period_start->format('d M'),
            $this->period_end->format('d M')
        );
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
