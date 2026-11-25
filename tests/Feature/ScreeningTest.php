<?php

namespace Tests\Feature;

use App\Services\DiabetesScreeningService;
use Tests\TestCase;

class ScreeningTest extends TestCase
{
    public function test_rute_screening_terpisah_sudah_dihapus(): void
    {
        $this->get('/screening')->assertNotFound();
    }

    public function test_prediksi_versi_b_posbindu_skenario_risiko_tinggi(): void
    {
        $service = app(DiabetesScreeningService::class);
        $data = $service->predict('versi_b', [
            'Pregnancies' => 4,
            'BloodPressure' => 76,
            'SkinThickness' => 30,
            'BMI' => 33.6,
            'DiabetesPedigreeFunction' => 0.627,
            'Age' => 45,
        ], 0.40);

        $this->assertSame(1, $data['prediction']);
        $this->assertSame('versi_b', $data['mode']);
        $this->assertSame('Prioritas Pemeriksaan Laboratorium Lanjut', $data['risk_label']);
        $this->assertEqualsWithDelta(63.81, $data['probability_pct'], 0.1);
        $this->assertNotEmpty($data['top_factors']);
    }

    public function test_prediksi_versi_a_lengkap_dengan_imputasi_insulin(): void
    {
        $service = app(DiabetesScreeningService::class);
        $data = $service->predict('versi_a', [
            'Pregnancies' => 4,
            'Glucose' => 148,
            'BloodPressure' => 76,
            'SkinThickness' => 30,
            'Insulin' => 0, // Nilai 0 wajib diimputasi median
            'BMI' => 33.6,
            'DiabetesPedigreeFunction' => 0.627,
            'Age' => 45,
        ], 0.35);

        $this->assertSame(1, $data['prediction']);
        $this->assertSame('versi_a', $data['mode']);
        $this->assertSame('Prioritas Pemeriksaan Laboratorium Lanjut', $data['risk_label']);
        $this->assertEqualsWithDelta(77.57, $data['probability_pct'], 0.1);
        $this->assertContains('Insulin', $data['imputed_fields']);
    }

    public function test_mode_tidak_valid_melempar_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(DiabetesScreeningService::class)->predict('versi_c', []);
    }
}
