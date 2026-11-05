<?php

namespace App\Models;

use App\Services\TriageClassifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    protected $guarded = [];

    public const STATUS_LABELS = [
        'menunggu' => 'Menunggu',
        'dipanggil' => 'Dipanggil',
        'darurat' => 'Dokter dihubungi',
        'ditangani' => 'Sedang diperiksa',
        'observasi' => 'Observasi',
        'selesai' => 'Selesai',
    ];

    protected function casts(): array
    {
        return [
            'is_manual_priority' => 'boolean',
            'outcome' => 'boolean',
            'prescriptions' => 'array',
            'insulin' => 'decimal:1',
            'bmi' => 'decimal:1',
            'dpf' => 'decimal:3',
            'called_at' => 'datetime',
            'emergency_called_at' => 'datetime',
            'handling_started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /* ---------- Relasi ---------- */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function escalator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /* ---------- Scope ---------- */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', '!=', 'selesai');
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByRaw("CASE priority WHEN 'tinggi' THEN 1 WHEN 'sedang' THEN 2 ELSE 3 END")
            ->orderBy('created_at');
    }

    /* ---------- Tampilan ---------- */

    public function getPriorityLabelAttribute(): string
    {
        return TriageClassifier::LABELS[$this->priority] ?? ucfirst($this->priority);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'P' ? 'Perempuan' : 'Laki-laki';
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name));

        return mb_strtoupper(mb_substr($words[0] ?? '', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
    }

    public function getBpLabelAttribute(): string
    {
        return ($this->systolic_bp ?? '–') . '/' . $this->diastolic_bp;
    }

    public function getKeyResultsAttribute(): string
    {
        $parts = ['TD: ' . $this->bp_label];
        if ($this->heart_rate) {
            $parts[] = 'Nadi: ' . $this->heart_rate;
        }
        $parts[] = 'GDS: ' . $this->glucose;

        return implode(' • ', $parts);
    }

    /** Nada warna (danger | warn | normal) per parameter, dipakai chip & kartu metrik. */
    public function tone(string $metric): string
    {
        return match ($metric) {
            'bp' => ($this->systolic_bp >= 180 || $this->diastolic_bp >= 120) ? 'danger'
                : (($this->systolic_bp >= 140 || $this->diastolic_bp >= 90) ? 'warn' : 'normal'),
            'hr' => ($this->heart_rate && ($this->heart_rate >= 120 || $this->heart_rate < 50)) ? 'danger'
                : (($this->heart_rate && $this->heart_rate > 100) ? 'warn' : 'normal'),
            'glucose' => ($this->glucose >= 300 || $this->glucose < 70) ? 'danger'
                : ($this->glucose >= 126 ? 'warn' : 'normal'),
            'bmi' => $this->bmi >= 35 ? 'danger' : ($this->bmi >= 25 ? 'warn' : 'normal'),
            'dpf' => $this->dpf >= 0.5 ? 'warn' : 'normal',
            'insulin' => $this->insulin >= 166 ? 'warn' : 'normal',
            'age' => $this->age >= 45 ? 'warn' : 'normal',
            'spo2' => ($this->spo2 && $this->spo2 < 90) ? 'danger' : (($this->spo2 && $this->spo2 < 95) ? 'warn' : 'normal'),
            default => 'normal',
        };
    }

    public function glucoseNote(): string
    {
        return match (true) {
            $this->glucose >= 300 => 'Hiperglikemia berat',
            $this->glucose >= 200 => 'Hiperglikemia signifikan',
            $this->glucose >= 126 => 'Di atas batas normal',
            $this->glucose < 70 => 'Hipoglikemia',
            default => 'Dalam batas normal',
        };
    }

    public function bmiNote(): string
    {
        return match (true) {
            $this->bmi < 18.5 => 'Kurus',
            $this->bmi < 25 => 'Normal',
            $this->bmi < 30 => 'Berat badan lebih',
            $this->bmi < 35 => 'Obesitas I',
            default => 'Obesitas II+',
        };
    }

    public function bpNote(): string
    {
        return match (true) {
            $this->diastolic_bp >= 120 => 'Diastolik sangat tinggi',
            $this->diastolic_bp >= 90 => 'Diastolik tinggi',
            $this->diastolic_bp >= 80 => 'Diastolik batas atas',
            default => 'Diastolik normal',
        };
    }
}
