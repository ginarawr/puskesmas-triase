<?php

namespace App\Services;

/**
 * Penentu prioritas triase berbasis aturan (rule-based).
 *
 * PENTING: ini bukan model ML terlatih dan bukan alat diagnosis. Ambang batas di bawah
 * adalah nilai awal untuk demo; WAJIB ditinjau tenaga medis sebelum dipakai sungguhan.
 * Semua ambang ada di satu tempat (konstanta) supaya mudah disesuaikan.
 */
class TriageClassifier
{
    public const LABELS = ['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'];

    // Kriteria gawat darurat -> langsung Prioritas Tinggi
    private const CRIT_GLUCOSE_HIGH = 400;   // mg/dL
    private const CRIT_GLUCOSE_LOW = 54;     // mg/dL
    private const CRIT_SYSTOLIC = 180;       // mmHg
    private const CRIT_DIASTOLIC = 120;      // mmHg
    private const CRIT_HR_HIGH = 130;        // x/menit
    private const CRIT_HR_LOW = 40;          // x/menit
    private const CRIT_SPO2 = 90;            // %

    // Skor risiko: Prioritas Sedang bila total >= ambang
    private const MEDIUM_SCORE = 3;

    public function evaluate(array $in): array
    {
        $glucose = $this->num($in, 'glucose');
        $dia = $this->num($in, 'diastolic_bp');
        $sys = $this->num($in, 'systolic_bp');
        $hr = $this->num($in, 'heart_rate');
        $spo2 = $this->num($in, 'spo2');
        $bmi = $this->num($in, 'bmi');
        $dpf = $this->num($in, 'dpf');
        $age = $this->num($in, 'age');

        $critical = [];
        if ($glucose !== null && ($glucose >= self::CRIT_GLUCOSE_HIGH || ($glucose > 0 && $glucose < self::CRIT_GLUCOSE_LOW))) {
            $critical[] = 'Glukosa ' . $this->fmt($glucose) . ' mg/dL';
        }
        if (($sys !== null && $sys >= self::CRIT_SYSTOLIC) || ($dia !== null && $dia >= self::CRIT_DIASTOLIC)) {
            $critical[] = 'TD ' . ($sys !== null ? $this->fmt($sys) : '–') . '/' . ($dia !== null ? $this->fmt($dia) : '–') . ' mmHg';
        }
        if ($hr !== null && ($hr >= self::CRIT_HR_HIGH || $hr < self::CRIT_HR_LOW)) {
            $critical[] = 'Nadi ' . $this->fmt($hr) . ' x/menit';
        }
        if ($spo2 !== null && $spo2 > 0 && $spo2 < self::CRIT_SPO2) {
            $critical[] = 'SpO2 ' . $this->fmt($spo2) . '%';
        }

        $score = 0;
        $triggers = [];
        if ($glucose !== null && $glucose >= 200) {
            $score += 3;
            $triggers[] = 'Glukosa ' . $this->fmt($glucose) . ' mg/dL';
        } elseif ($glucose !== null && $glucose >= 126) {
            $score += 2;
            $triggers[] = 'Glukosa ' . $this->fmt($glucose) . ' mg/dL';
        }
        if ($bmi !== null && $bmi >= 30) {
            $score += 1;
            $triggers[] = 'BMI ' . $this->fmt($bmi, 1);
        }
        if ($dpf !== null && $dpf >= 0.5) {
            $score += 1;
            $triggers[] = 'DPF ' . $this->fmt($dpf, 3);
        }
        if ($age !== null && $age >= 45) {
            $score += 1;
            $triggers[] = 'Usia ' . $this->fmt($age) . ' th';
        }
        if (($sys !== null && $sys >= 140) || ($dia !== null && $dia >= 90)) {
            $score += 1;
            $triggers[] = 'Tekanan darah tinggi';
        }

        if ($critical) {
            $priority = 'tinggi';
            $triggers = $critical;
        } elseif ($score >= self::MEDIUM_SCORE) {
            $priority = 'sedang';
        } else {
            $priority = 'rendah';
        }

        $copy = [
            'tinggi' => [
                'headline' => 'Prioritas Tinggi – Segera hubungi dokter & bawa ke UGD',
                'route' => 'Alur: hubungi dokter jaga, pasien dibawa ke UGD.',
            ],
            'sedang' => [
                'headline' => 'Prioritas Sedang – Antrean didahulukan & pantau glukosa',
                'route' => 'Alur: masuk antrean didahulukan, diperiksa setelah kasus darurat.',
            ],
            'rendah' => [
                'headline' => 'Prioritas Rendah – Antrean normal',
                'route' => 'Alur: antrean normal, kontrol rutin.',
            ],
        ][$priority];

        return [
            'priority' => $priority,
            'label' => self::LABELS[$priority],
            'outcome' => $priority === 'rendah' ? 0 : 1,
            'score' => $score,
            'headline' => $copy['headline'],
            'route' => $copy['route'],
            'triggers' => array_slice($triggers, 0, 4),
        ];
    }

    private function num(array $in, string $key): ?float
    {
        $v = $in[$key] ?? null;

        return ($v === null || $v === '' || ! is_numeric($v)) ? null : (float) $v;
    }

    private function fmt(float $n, int $decimals = 0): string
    {
        return number_format($n, $decimals, '.', '');
    }
}
