<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScreeningTest extends TestCase
{
    public function test_halaman_skrining_dapat_diakses(): void
    {
        $response = $this->get('/screening');

        $response->assertStatus(200);
        $response->assertSee('Skrining Risiko Diabetes');
        $response->assertSee('Peringatan Klinis');
        $response->assertSee('Puskesmas Harapan Sehat');
    }

    public function test_prediksi_versi_b_posbindu_skenario_risiko_tinggi(): void
    {
        $payload = [
            'mode' => 'versi_b',
            'Pregnancies' => 4,
            'BloodPressure' => 76,
            'SkinThickness' => 30,
            'BMI' => 33.6,
            'DiabetesPedigreeFunction' => 0.627,
            'Age' => 45,
            'threshold' => 0.40,
        ];

        $response = $this->postJson('/predict', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'prediction' => 1,
            'mode' => 'versi_b',
            'risk_label' => 'Prioritas Pemeriksaan Laboratorium Lanjut',
        ]);

        $data = $response->json();
        $this->assertEqualsWithDelta(63.81, $data['probability_pct'], 0.1);
        $this->assertNotEmpty($data['top_factors']);
    }

    public function test_prediksi_versi_a_lengkap_dengan_imputasi_insulin(): void
    {
        $payload = [
            'mode' => 'versi_a',
            'Pregnancies' => 4,
            'Glucose' => 148,
            'BloodPressure' => 76,
            'SkinThickness' => 30,
            'Insulin' => 0, // Nilai 0 wajib diimputasi median
            'BMI' => 33.6,
            'DiabetesPedigreeFunction' => 0.627,
            'Age' => 45,
            'threshold' => 0.35,
        ];

        $response = $this->postJson('/predict', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'prediction' => 1,
            'mode' => 'versi_a',
            'risk_label' => 'Prioritas Pemeriksaan Laboratorium Lanjut',
        ]);

        $data = $response->json();
        $this->assertEqualsWithDelta(77.57, $data['probability_pct'], 0.1);
        $this->assertContains('Insulin', $data['imputed_fields']);
    }

    public function test_mode_tidak_valid_mengembalikan_error_422(): void
    {
        $response = $this->postJson('/predict', ['mode' => 'versi_c']);
        $response->assertStatus(422);
    }
}
