<?php

namespace Tests\Unit;

use App\Services\TriageClassifier;
use PHPUnit\Framework\TestCase;

class TriageClassifierTest extends TestCase
{
    public function test_kondisi_kritis_menjadi_prioritas_tinggi(): void
    {
        $r = (new TriageClassifier())->evaluate(['systolic_bp' => 185, 'diastolic_bp' => 110, 'glucose' => 210]);

        $this->assertSame('tinggi', $r['priority']);
        $this->assertSame(1, $r['outcome']);
    }

    public function test_beberapa_faktor_risiko_menjadi_prioritas_sedang(): void
    {
        $r = (new TriageClassifier())->evaluate([
            'glucose' => 168, 'diastolic_bp' => 84, 'bmi' => 31.4, 'dpf' => 0.672, 'age' => 48,
        ]);

        $this->assertSame('sedang', $r['priority']);
        $this->assertSame(1, $r['outcome']);
    }

    public function test_nilai_normal_menjadi_prioritas_rendah(): void
    {
        $r = (new TriageClassifier())->evaluate([
            'glucose' => 100, 'diastolic_bp' => 70, 'bmi' => 22, 'dpf' => 0.2, 'age' => 30,
        ]);

        $this->assertSame('rendah', $r['priority']);
        $this->assertSame(0, $r['outcome']);
    }

    public function test_isian_kosong_tidak_error(): void
    {
        $r = (new TriageClassifier())->evaluate([]);

        $this->assertSame('rendah', $r['priority']);
    }
}
